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

    // MODÜL 13: Gelişim Karnesi Kaydı
    public function storeDevelopmentRecord(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'term_label' => 'required|string|max:100',
            'current_level' => 'required|string|max:100',
            'flexibility_score' => 'required|integer|min:0|max:100',
            'strength_score' => 'required|integer|min:0|max:100',
            'balance_score' => 'required|integer|min:0|max:100',
            'coordination_score' => 'required|integer|min:0|max:100',
            'discipline_score' => 'required|integer|min:0|max:100',
            'coach_comment' => 'nullable|string',
        ]);

        $student = Student::with('group')->find($validated['student_id']);
        $this->assertCoachOwnsGroup($student?->group_id);

        $record = DevelopmentRecord::create([
            'student_id' => $validated['student_id'],
            // Hangi antrenörün kaydettiği değil, öğrencinin GRUBUNUN atanmış
            // antrenörü — ilk bulunan rastgele antrenör değil.
            'coach_id' => $student?->group?->coach_id ?? $this->getAuthenticatedStaff()?->id,
            'term_label' => $validated['term_label'],
            'current_level' => $validated['current_level'],
            'flexibility_score' => $validated['flexibility_score'],
            'strength_score' => $validated['strength_score'],
            'balance_score' => $validated['balance_score'],
            'coordination_score' => $validated['coordination_score'],
            'discipline_score' => $validated['discipline_score'],
            'skills' => [
                'Temel Duruş ve Esneklik' => 'Başarılı',
                'Takla & Çember' => 'Başarılı',
                'Amut & Denge' => 'Gelişiyor',
            ],
            'coach_comment' => $validated['coach_comment'] ?? null,
        ]);

        AuditLog::record('karne_eklendi', "{$student?->full_name} için yeni gelişim karnesi oluşturuldu.", 'DevelopmentRecord', $record->id);
        return redirect()->route('admin.index', ['tab' => 'gelisim'])->with('success', 'Öğrenci gelişim karnesi oluşturuldu ve veli ekranında yayınlandı!');
    }

    public function updateDevelopmentRecord(Request $request, DevelopmentRecord $record)
    {
        $this->assertCoachOwnsGroup($record->student?->group_id);
        $request->validate([
            'flexibility_score' => 'nullable|integer|min:0|max:100',
            'strength_score' => 'nullable|integer|min:0|max:100',
            'balance_score' => 'nullable|integer|min:0|max:100',
            'coordination_score' => 'nullable|integer|min:0|max:100',
            'discipline_score' => 'nullable|integer|min:0|max:100',
        ]);
        $record->update([
            'term_label' => $request->input('term_label', $record->term_label),
            'current_level' => $request->input('current_level', $record->current_level),
            'flexibility_score' => (int) $request->input('flexibility_score', $record->flexibility_score),
            'strength_score' => (int) $request->input('strength_score', $record->strength_score),
            'balance_score' => (int) $request->input('balance_score', $record->balance_score),
            'coordination_score' => (int) $request->input('coordination_score', $record->coordination_score),
            'discipline_score' => (int) $request->input('discipline_score', $record->discipline_score),
            'coach_comment' => $request->input('coach_comment', $record->coach_comment),
        ]);
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
