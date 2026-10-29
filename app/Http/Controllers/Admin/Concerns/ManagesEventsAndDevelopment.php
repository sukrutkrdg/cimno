<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\AuditLog;
use App\Models\DevelopmentRecord;
use App\Models\Event;
use App\Models\Student;
use Illuminate\Http\Request;

trait ManagesEventsAndDevelopment
{
    // MODÜL 12 & 13: Etkinlik Ekle / Düzenle & Gelişim Karnesi Düzenle
    public function storeEvent(Request $request)
    {
        $ev = Event::create([
            'title' => $request->input('title', 'Yeni Etkinlik'),
            'type' => $request->input('type', 'show'),
            'event_date' => $request->input('event_date', today()->addMonth()->toDateString()),
            'location' => $request->input('location', 'Tekirdağ Enerjik Cimnastik Salonu'),
            'quota' => (int) $request->input('quota', 40),
            'fee' => (float) $request->input('fee', 0),
            'description' => $request->input('description', ''),
            'is_active' => true,
        ]);
        AuditLog::record('etkinlik_eklendi', "Yeni etkinlik eklendi: {$ev->title}", 'Event', $ev->id);
        return redirect()->route('admin.index', ['tab' => 'etkinlikler'])->with('success', "'{$ev->title}' etkinliği eklendi!");
    }

    public function updateEvent(Request $request, Event $event)
    {
        $request->validate([
            'event_date' => 'nullable|date',
            'quota' => 'nullable|integer|min:1',
            'fee' => 'nullable|numeric|min:0',
        ]);
        $event->update([
            'title' => $request->input('title', $event->title),
            'event_date' => $request->input('event_date', $event->event_date?->toDateString()),
            'location' => $request->input('location', $event->location),
            'quota' => (int) $request->input('quota', $event->quota),
            'fee' => (float) $request->input('fee', $event->fee),
            'description' => $request->input('description', $event->description),
        ]);
        AuditLog::record('etkinlik_guncellendi', "Etkinlik güncellendi: {$event->title}", 'Event', $event->id);
        return redirect()->route('admin.index', ['tab' => 'etkinlikler'])->with('success', "'{$event->title}' etkinliği güncellendi!");
    }

    public function destroyEvent(Event $event)
    {
        $title = $event->title;
        $event->delete();
        AuditLog::record('etkinlik_silindi', "Etkinlik silindi: {$title}");
        return redirect()->route('admin.index', ['tab' => 'etkinlikler'])->with('success', "'{$title}' etkinliği silindi.");
    }

    // MODÜL 13: Gelişim Karnesi Kaydı — salonun basılı "GELİŞİM TAKİP KARNESİ"
    // formuna göre: 3 kategori × 4 kriter, her biri 1-4 ölçeğinde.
    private const PDF_KARNE_CRITERIA = [
        'phys_flexibility', 'phys_balance_coordination', 'phys_strength_endurance', 'phys_focus',
        'tech_forward_backward_roll', 'tech_handstand_wheel', 'tech_eagle_split', 'tech_flexibility_cooldown',
        'social_class_participation', 'social_communication', 'social_command_following', 'social_goal_progress',
    ];

    public function storeDevelopmentRecord(Request $request)
    {
        $rules = ['student_id' => 'required|exists:students,id', 'term_label' => 'required|string|max:100', 'current_level' => 'required|string|max:100', 'coach_comment' => 'nullable|string'];
        foreach (self::PDF_KARNE_CRITERIA as $crit) {
            $rules[$crit] = 'required|integer|min:1|max:4';
        }
        $validated = $request->validate($rules);

        $student = Student::with('group')->find($validated['student_id']);
        $this->assertCoachOwnsGroup($student?->group_id);

        $record = DevelopmentRecord::create([
            'student_id' => $validated['student_id'],
            // Hangi antrenörün kaydettiği değil, öğrencinin GRUBUNUN atanmış
            // antrenörü — ilk bulunan rastgele antrenör değil.
            'coach_id' => $student?->group?->coach_id ?? $this->getAuthenticatedStaff()?->id,
            'term_label' => $validated['term_label'],
            'current_level' => $validated['current_level'],
            ...array_intersect_key($validated, array_flip(self::PDF_KARNE_CRITERIA)),
            'coach_comment' => $validated['coach_comment'] ?? null,
        ]);

        AuditLog::record('karne_eklendi', "{$student?->full_name} için yeni gelişim karnesi oluşturuldu.", 'DevelopmentRecord', $record->id);
        return redirect()->route('admin.index', ['tab' => 'gelisim'])->with('success', 'Öğrenci gelişim karnesi oluşturuldu ve veli ekranında yayınlandı!');
    }

    public function updateDevelopmentRecord(Request $request, DevelopmentRecord $record)
    {
        $this->assertCoachOwnsGroup($record->student?->group_id);

        $updateData = [
            'term_label' => $request->input('term_label', $record->term_label),
            'current_level' => $request->input('current_level', $record->current_level),
            'coach_comment' => $request->input('coach_comment', $record->coach_comment),
        ];

        if ($record->isPdfFormat()) {
            // Yeni formattaki bir kayıt her zaman yeni (1-4 ölçekli) alanlarla düzenlenir.
            $rules = [];
            foreach (self::PDF_KARNE_CRITERIA as $crit) {
                $rules[$crit] = 'nullable|integer|min:1|max:4';
            }
            $validated = $request->validate($rules);
            foreach (self::PDF_KARNE_CRITERIA as $crit) {
                $updateData[$crit] = $request->filled($crit) ? (int) $validated[$crit] : $record->{$crit};
            }
        } else {
            // Eski (0-100 puanlı) formattaki bir kayıt, arşivdeki haliyle kendi
            // alanlarından düzenlenmeye devam eder — yeni formata dönüştürülmez.
            $validated = $request->validate([
                'flexibility_score' => 'nullable|integer|min:0|max:100',
                'strength_score' => 'nullable|integer|min:0|max:100',
                'balance_score' => 'nullable|integer|min:0|max:100',
                'coordination_score' => 'nullable|integer|min:0|max:100',
                'discipline_score' => 'nullable|integer|min:0|max:100',
            ]);
            foreach (['flexibility_score', 'strength_score', 'balance_score', 'coordination_score', 'discipline_score'] as $legacy) {
                $updateData[$legacy] = $request->filled($legacy) ? (int) $validated[$legacy] : $record->{$legacy};
            }
        }

        $record->update($updateData);

        AuditLog::record('karne_guncellendi', 'Gelişim karnesi güncellendi.', 'DevelopmentRecord', $record->id);
        return redirect()->route('admin.index', ['tab' => 'gelisim'])->with('success', 'Gelişim karnesi güncellendi!');
    }

    public function destroyDevelopmentRecord(DevelopmentRecord $record)
    {
        $this->assertCoachOwnsGroup($record->student?->group_id);
        $record->delete();
        AuditLog::record('karne_silindi', 'Gelişim karnesi kaydı silindi.');
        return redirect()->route('admin.index', ['tab' => 'gelisim'])->with('success', 'Gelişim karnesi silindi.');
    }
}
