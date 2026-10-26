<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CoachDataIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function makeCoachWithGroupAndStudent(string $suffix): array
    {
        $branch = Branch::create(['name' => "Test Branş {$suffix}", 'slug' => "test-brans-izole-{$suffix}", 'age_range' => '4-12', 'is_active' => true]);
        $coach = User::create([
            'name' => "Antrenör {$suffix}",
            'email' => "antrenor-izole-{$suffix}@test.local",
            'phone' => '532' . str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT),
            'role' => 'coach',
            'compensation_type' => 'none',
            'password' => Hash::make('gercek-sifre-123'),
            'is_active' => true,
            'permissions' => ['only_own_groups' => true, 'take_attendance' => true, 'add_development' => true],
        ]);
        $group = Group::create(['branch_id' => $branch->id, 'coach_id' => $coach->id, 'name' => "Grup {$suffix}", 'capacity' => 10, 'is_active' => true]);
        $student = Student::create([
            'full_name' => "Gizli Öğrenci {$suffix}",
            'branch_id' => $branch->id,
            'group_id' => $group->id,
            'parent_name' => 'Veli',
            'parent_phone' => '5000000' . random_int(0, 9),
            'status' => 'active',
            'consecutive_absences' => 2,
        ]);

        return [$coach, $group, $student];
    }

    public function test_coach_cannot_see_another_coachs_students_on_the_dashboard_attendance_or_development_tabs(): void
    {
        [$coachA, $groupA, $studentA] = $this->makeCoachWithGroupAndStudent('A');
        [$coachB, $groupB, $studentB] = $this->makeCoachWithGroupAndStudent('B');

        // B antrenörü kendi öğrencisi için yoklama ve gelişim karnesi oluşturuyor.
        $this->withSession(['admin_user_id' => $coachB->id])->post(route('admin.attendance.store'), [
            'group_id' => $groupB->id,
            'attendance_date' => today()->toDateString(),
            'statuses' => [$studentB->id => 'present'],
        ])->assertRedirect();

        $this->withSession(['admin_user_id' => $coachB->id])->post(route('admin.development.store'), [
            'student_id' => $studentB->id,
            'term_label' => 'Güz Dönemi',
            'current_level' => 'Başlangıç',
            'flexibility_score' => 50,
            'strength_score' => 50,
            'balance_score' => 50,
            'coordination_score' => 50,
            'discipline_score' => 50,
        ])->assertRedirect();

        // A antrenörü kendi panosunda B'nin öğrencisini HİÇBİR sekmede görmemeli.
        $asCoachA = $this->withSession(['admin_user_id' => $coachA->id]);

        $pano = $asCoachA->get('/panel');
        $pano->assertOk()->assertDontSee('Gizli Öğrenci B');

        $yoklama = $this->withSession(['admin_user_id' => $coachA->id])->get('/panel?tab=yoklama');
        $yoklama->assertOk()->assertDontSee('Gizli Öğrenci B')->assertSee('Gizli Öğrenci A');

        $gelisim = $this->withSession(['admin_user_id' => $coachA->id])->get('/panel?tab=gelisim');
        $gelisim->assertOk()->assertDontSee('Gizli Öğrenci B');

        // Sağlık/devamsızlık uyarıları da kendi grubuyla sınırlı olmalı.
        $studentB->update(['medical_report_expires_at' => now()->addDays(5)]);
        $panoAgain = $this->withSession(['admin_user_id' => $coachA->id])->get('/panel');
        $panoAgain->assertOk()->assertDontSee('Gizli Öğrenci B');
    }
}
