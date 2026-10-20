<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Student;
use App\Models\User;
use App\Models\WhatsappMessage;
use Illuminate\Http\Request;

trait ManagesAttendance
{
    // MODÜL 6: Grup Bazlı Tek Dokunuşla Yoklama Kaydı
    public function storeAttendance(Request $request)
    {
        $groupId = $request->input('group_id');
        $this->assertCoachOwnsGroup($groupId ? (int) $groupId : null);
        $date = $request->input('attendance_date', today()->toDateString());
        $statuses = $request->input('statuses', []);
        $notes = $request->input('notes', []);

        foreach ($statuses as $studentId => $status) {
            Attendance::updateOrCreate(
                [
                    'group_id' => $groupId,
                    'student_id' => $studentId,
                    'attendance_date' => $date,
                ],
                [
                    'coach_id' => User::where('role', 'coach')->value('id'),
                    'status' => $status,
                    'note' => $notes[$studentId] ?? null,
                ]
            );

            $student = Student::find($studentId);
            if ($student) {
                if ($status === 'absent') {
                    $student->increment('consecutive_absences');
                    if ($student->consecutive_absences >= 2) {
                        WhatsappMessage::create([
                            'student_id' => $student->id,
                            'recipient_name' => $student->parent_name,
                            'recipient_phone' => $student->parent_phone,
                            'message_text' => "Merhaba {$student->parent_name}, sporcumuz {$student->full_name} üst üste {$student->consecutive_absences} antrenmana katılamamıştır. Her şeyin yolunda olduğunu umuyoruz 🙏 Telafi planlaması için bize yazabilirsiniz.",
                            'status' => 'queued',
                        ]);
                    }
                } elseif ($status === 'excused') {
                    $student->increment('makeup_credits');
                } elseif ($status === 'present') {
                    $student->update(['consecutive_absences' => 0]);
                } elseif ($status === 'makeup' && $student->makeup_credits > 0) {
                    $student->decrement('makeup_credits');
                }
            }
        }

        AuditLog::record('yoklama_alindi', "Grup #{$groupId} için {$date} tarihli yoklama kaydedildi.", 'Group', (int) $groupId);

        return redirect()->route('admin.index', ['tab' => 'yoklama'])->with('success', 'Grup yoklaması kaydedildi, telafi hakları güncellendi ve devamsızlık uyarıları WhatsApp kuyruğuna eklendi.');
    }

    public function destroyAttendance(Attendance $attendance)
    {
        $this->assertCoachOwnsGroup($attendance->group_id);
        $attendance->delete();
        AuditLog::record('yoklama_silindi', 'Yoklama kaydı silindi.');
        return redirect()->route('admin.index', ['tab' => 'yoklama'])->with('success', 'Yoklama kaydı silindi.');
    }
}
