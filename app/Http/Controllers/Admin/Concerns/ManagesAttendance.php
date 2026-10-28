<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Group;
use App\Models\Student;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait ManagesAttendance
{
    // MODÜL 6: Grup Bazlı Tek Dokunuşla Yoklama Kaydı
    public function storeAttendance(Request $request)
    {
        $groupId = $request->input('group_id');
        $this->assertCoachOwnsGroup($groupId ? (int) $groupId : null);
        $request->validate([
            'statuses' => 'required|array',
            'statuses.*' => 'in:present,absent,excused,makeup',
        ]);
        $date = $request->input('attendance_date', today()->toDateString());
        $statuses = $request->input('statuses', []);
        $notes = $request->input('notes', []);

        // Yoklamayı kaydeden kişi her zaman "bir" antrenör değil (sekreter de
        // alabilir); devamsızlık uyarısı ve antrenör hakediş hesabı için doğru
        // sahip, grubun ATANMIŞ antrenörüdür — ilk bulunan antrenör değil.
        $coachId = Group::find($groupId)?->coach_id ?? $this->getAuthenticatedStaff()?->id;

        DB::transaction(function () use ($groupId, $date, $statuses, $notes, $coachId) {
            foreach ($statuses as $studentId => $status) {
                // updateOrCreate'in eşleştirme sorgusu, attendance_date'i olduğu gibi
                // (saat bileşeni olmadan) karşılaştırır — ama Eloquent'in 'date' cast'i
                // yalnızca OKURKEN 'Y-m-d'ye kısaltır, YAZARKEN bağlantının tam
                // tarih-saat biçimini kullanır. Sonuç: aynı tarih için ikinci bir
                // yoklama gönderimi (ör. hatalı girilen durumu düzeltmek) var olan
                // kaydı asla bulamaz, tekrar INSERT dener ve UNIQUE kısıtını ihlal
                // ederek 500 hatası verirdi. whereDate() bu uyumsuzluğu by-pass eder.
                $attendance = Attendance::where('group_id', $groupId)
                    ->where('student_id', $studentId)
                    ->whereDate('attendance_date', $date)
                    ->first();

                if ($attendance) {
                    $attendance->update([
                        'coach_id' => $coachId,
                        'status' => $status,
                        'note' => $notes[$studentId] ?? null,
                    ]);
                } else {
                    Attendance::create([
                        'group_id' => $groupId,
                        'student_id' => $studentId,
                        'attendance_date' => $date,
                        'coach_id' => $coachId,
                        'status' => $status,
                        'note' => $notes[$studentId] ?? null,
                    ]);
                }

                $student = Student::find($studentId);
                if ($student) {
                    $this->recalculateAttendanceCounters($student);
                }
            }
        });

        AuditLog::record('yoklama_alindi', "Grup #{$groupId} için {$date} tarihli yoklama kaydedildi.", 'Group', (int) $groupId);

        return redirect()->route('admin.index', ['tab' => 'yoklama'])->with('success', 'Grup yoklaması kaydedildi, telafi hakları güncellendi ve devamsızlık uyarıları WhatsApp kuyruğuna eklendi.');
    }

    // Yoklama anında gözden kaçan ya da sonradan veliden gelen rapor/izin bilgisiyle
    // düzeltilmesi gereken bir kayıt için (ör. "Gelmedi" → "Raporlu" yapıp telafi hakkı
    // kazandırmak) tek kaydı güncelleme; eskiden bunun tek yolu grubun o güne ait TÜM
    // yoklamasını yeniden göndermekti — tek öğrenciyi düzeltirken yanlışlıkla aynı
    // günün diğer öğrencilerini de ezme riski taşıyordu.
    public function updateAttendance(Request $request, Attendance $attendance)
    {
        $this->assertCoachOwnsGroup($attendance->group_id);
        $validated = $request->validate([
            'status' => 'required|in:present,absent,excused,makeup',
            'note' => 'nullable|string|max:255',
        ]);

        $attendance->update([
            'status' => $validated['status'],
            'note' => $validated['note'] ?? $attendance->note,
        ]);

        $student = Student::find($attendance->student_id);
        if ($student) {
            $this->recalculateAttendanceCounters($student);
        }

        AuditLog::record('yoklama_guncellendi', "{$attendance->student?->full_name} için yoklama kaydı güncellendi: {$validated['status']}.", 'Attendance', $attendance->id);

        return redirect()->route('admin.index', ['tab' => 'yoklama'])->with('success', 'Yoklama kaydı güncellendi.');
    }

    public function destroyAttendance(Attendance $attendance)
    {
        $this->assertCoachOwnsGroup($attendance->group_id);
        $studentId = $attendance->student_id;

        DB::transaction(function () use ($attendance, $studentId) {
            $attendance->delete();
            $student = Student::find($studentId);
            if ($student) {
                $this->recalculateAttendanceCounters($student);
            }
        });

        AuditLog::record('yoklama_silindi', 'Yoklama kaydı silindi.');
        return redirect()->route('admin.index', ['tab' => 'yoklama'])->with('success', 'Yoklama kaydı silindi.');
    }

    /**
     * consecutive_absences ve makeup_credits, her storeAttendance çağrısında
     * increment/decrement edilen bağımsız sayaçlar olarak tutulursa, bir yoklamayı
     * düzeltmek (ör. "Gelmedi" → "Raporlu") ya da aynı formu iki kez göndermek
     * sayaçları kalıcı olarak gerçek durumdan saptırır (çift sayma). Bunun yerine
     * her iki değer de öğrencinin gerçek yoklama geçmişinden, çağrıldığı her
     * seferde sıfırdan yeniden hesaplanır — bu sayede sayaç her zaman mevcut
     * kayıtlarla tutarlıdır, aradaki düzeltme/silme adımlarından bağımsız olarak.
     */
    protected function recalculateAttendanceCounters(Student $student): void
    {
        $previousConsecutiveAbsences = $student->consecutive_absences;

        $consecutiveAbsences = 0;
        $recentAttendances = Attendance::where('student_id', $student->id)
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->get(['status']);

        foreach ($recentAttendances as $att) {
            if ($att->status === 'absent') {
                $consecutiveAbsences++;
            } elseif ($att->status === 'present') {
                break;
            }
            // 'excused' ve 'makeup' serinin dışında tutulur (ne sayılır ne de seriyi
            // bozar) — orijinal davranışla aynı: raporlu/telafi bir ders, üst üste
            // devamsızlık sayacını ne artırır ne sıfırlar.
        }

        $makeupCredits = max(0,
            Attendance::where('student_id', $student->id)->where('status', 'excused')->count()
            - Attendance::where('student_id', $student->id)->where('status', 'makeup')->count()
        );

        $student->update([
            'consecutive_absences' => $consecutiveAbsences,
            'makeup_credits' => $makeupCredits,
        ]);

        // Uyarı mesajı yalnızca sayaç yeni yeni 2'ye ulaştığında (önceki değer
        // 2'nin altındaysa) kuyruğa eklenir — aksi hâlde aynı yoklamayı tekrar
        // kaydetmek veli WhatsApp kuyruğunu aynı uyarıyla doldururdu.
        if ($consecutiveAbsences >= 2 && $previousConsecutiveAbsences < 2) {
            $tplAbsence = WhatsappTemplate::where('trigger_event', 'absence_warning')->where('is_active', true)->first();
            $text = $tplAbsence
                ? str_replace(
                    ['{veli_adi}', '{ogrenci_adi}', '{sayi}'],
                    [$student->parent_name, $student->full_name, (string) $consecutiveAbsences],
                    $tplAbsence->message_template
                )
                : "Merhaba {$student->parent_name}, sporcumuz {$student->full_name} üst üste {$consecutiveAbsences} antrenmana katılamamıştır. Her şeyin yolunda olduğunu umuyoruz 🙏 Telafi planlaması için bize yazabilirsiniz.";

            WhatsappMessage::create([
                'template_id' => $tplAbsence?->id,
                'student_id' => $student->id,
                'recipient_name' => $student->parent_name,
                'recipient_phone' => $student->parent_phone,
                'message_text' => $text,
                'status' => 'queued',
            ]);
        }
    }
}
