<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\AuditLog;
use App\Models\Group;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Http\Request;

trait ManagesGroupsAndSchedules
{
    // MODÜL 4 & 5: Yeni Grup Açıp Ders Programına Ekleme (Kabul Kriteri #2)
    // Not: Grup oluşturma antrenöre açık değildir — update/destroy/storeSchedule gibi
    // mevcut gruplar üzerindeki işlemler assertCoachOwnsGroup() ile korunuyordu, ama
    // "yeni grup aç" bir sahiplik kontrolüne tabi olamayacağından (henüz grup yok)
    // burada ayrıca engellenir.
    public function storeGroupAndSchedule(Request $request)
    {
        $authUser = $this->getAuthenticatedStaff();
        if ($authUser->role === 'coach' && $authUser->hasPermission('only_own_groups', true)) {
            return redirect()->route('admin.index', ['tab' => 'gruplar'])->with('error', 'Yeni grup açma yetkiniz yok, lütfen sekreterliğe başvurun.');
        }

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'coach_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:120',
            'level' => 'required|string|max:80',
            'age_group' => 'required|string|max:60',
            'area' => 'required|string|max:80',
            'capacity' => 'required|integer|min:1|max:50',
            'day_of_week' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        // Alan ve saat çakışma kontrolü
        $conflict = Schedule::where('day_of_week', $validated['day_of_week'])
            ->where('area', $validated['area'])
            ->where('is_cancelled', false)
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']]);
            })->first();

        $group = Group::create([
            'branch_id' => $validated['branch_id'],
            'coach_id' => $validated['coach_id'] ?? null,
            'name' => $validated['name'],
            'level' => $validated['level'],
            'age_group' => $validated['age_group'],
            'area' => $validated['area'],
            'capacity' => $validated['capacity'],
            'is_active' => true,
        ]);

        Schedule::create([
            'group_id' => $group->id,
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'area' => $validated['area'],
        ]);

        AuditLog::record('grup_ve_program_eklendi', "Yeni grup ({$group->name}) ve ders programı ({$validated['day_of_week']} {$validated['start_time']}) eklendi.", 'Group', $group->id);

        $msg = "Yeni grup '{$group->name}' açıldı ve haftalık ders programına eklendi.";
        if ($conflict) {
            $msg .= " (Uyarı: {$validated['area']} alanında aynı saatte başka bir ders çakışması tespit edildi, lütfen kontrol edin.)";
        }

        return redirect()->route('admin.index', ['tab' => 'gruplar'])->with('success', $msg);
    }

    // MODÜL 4 & 5: Mevcut Grubu Düzenle & Gruba Yeni Ders Saati Ekle / Düzenle
    public function updateGroup(Request $request, Group $group)
    {
        $this->assertCoachOwnsGroup($group->id);
        $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'coach_id' => 'nullable|exists:users,id',
            'capacity' => 'nullable|integer|min:1|max:60',
        ]);
        $group->update([
            'name' => $request->input('name', $group->name),
            'branch_id' => $request->input('branch_id', $group->branch_id),
            'coach_id' => $request->input('coach_id') ?: null,
            'age_group' => $request->input('age_group', $group->age_group),
            'level' => $request->input('level', $group->level),
            'area' => $request->input('area', $group->area),
            'capacity' => (int) $request->input('capacity', $group->capacity),
        ]);
        AuditLog::record('grup_guncellendi', "Grup güncellendi: {$group->name}", 'Group', $group->id);
        $tab = $request->input('return_tab', 'gruplar');
        return redirect()->route('admin.index', ['tab' => $tab])->with('success', "'{$group->name}' grup bilgileri güncellendi!");
    }

    public function destroyGroup(Group $group)
    {
        $this->assertCoachOwnsGroup($group->id);
        $name = $group->name;
        Schedule::where('group_id', $group->id)->delete();
        Student::where('group_id', $group->id)->update(['group_id' => null]);
        $group->delete();
        AuditLog::record('grup_silindi', "Grup ve ders programı silindi: {$name}");
        return redirect()->route('admin.index', ['tab' => 'gruplar'])->with('success', "'{$name}' grubu ve bağlı ders saatleri silindi.");
    }

    public function storeSchedule(Request $request, Group $group)
    {
        $this->assertCoachOwnsGroup($group->id);
        $sch = Schedule::create([
            'group_id' => $group->id,
            'day_of_week' => $request->input('day_of_week', 'Cumartesi'),
            'start_time' => $request->input('start_time', '10:00'),
            'end_time' => $request->input('end_time', '11:00'),
            'area' => $request->input('area', $group->area ?? 'Ana Minder'),
        ]);
        AuditLog::record('program_eklendi', "{$group->name} grubuna yeni ders saati eklendi ({$sch->day_of_week} {$sch->start_time}).", 'Schedule', $sch->id);
        return redirect()->route('admin.index', ['tab' => 'gruplar'])->with('success', "'{$group->name}' grubuna yeni ders saati ({$sch->day_of_week} {$sch->start_time}) eklendi!");
    }

    public function updateSchedule(Request $request, Schedule $schedule)
    {
        $this->assertCoachOwnsGroup($schedule->group_id);
        $request->validate([
            'day_of_week' => 'nullable|in:Pazartesi,Salı,Çarşamba,Perşembe,Cuma,Cumartesi,Pazar',
        ]);
        $schedule->update([
            'day_of_week' => $request->input('day_of_week', $schedule->day_of_week),
            'start_time' => $request->input('start_time', $schedule->start_time),
            'end_time' => $request->input('end_time', $schedule->end_time),
            'area' => $request->input('area', $schedule->area),
        ]);
        AuditLog::record('program_guncellendi', "Ders saati güncellendi: {$schedule->day_of_week} {$schedule->start_time}", 'Schedule', $schedule->id);
        return redirect()->route('admin.index', ['tab' => 'gruplar'])->with('success', 'Ders saati güncellendi!');
    }

    public function destroySchedule(Schedule $schedule)
    {
        $this->assertCoachOwnsGroup($schedule->group_id);
        $info = "{$schedule->day_of_week} {$schedule->start_time}";
        $schedule->delete();
        AuditLog::record('program_silindi', "Ders saati silindi: {$info}");
        return redirect()->route('admin.index', ['tab' => 'gruplar'])->with('success', "Ders programı saati ({$info}) silindi.");
    }
}
