<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAttendanceReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function makeSuperAdmin(): User
    {
        return User::create([
            'name' => 'Test Yönetici',
            'email' => 'yonetici@test.local',
            'phone' => '05321234567',
            'role' => 'super_admin',
            'compensation_type' => 'none',
            'password' => Hash::make('gercek-sifre-123'),
            'is_active' => true,
            'permissions' => ['all' => true],
        ]);
    }

    public function test_attendance_report_renders_with_summary_and_chart_data(): void
    {
        $admin = $this->makeSuperAdmin();
        $branch = Branch::create(['name' => 'Artistik Cimnastik', 'slug' => 'artistik', 'age_range' => '4-12', 'is_active' => true]);
        $coach = User::create([
            'name' => 'Antrenör Test', 'email' => 'antrenor@test.local', 'phone' => '05329876543',
            'role' => 'coach', 'compensation_type' => 'none', 'password' => Hash::make('x'), 'is_active' => true,
        ]);
        $group = Group::create(['branch_id' => $branch->id, 'coach_id' => $coach->id, 'name' => 'A Grubu', 'capacity' => 10, 'is_active' => true]);
        $student = Student::create(['full_name' => 'Test Öğrenci', 'branch_id' => $branch->id, 'group_id' => $group->id, 'parent_name' => 'Veli', 'parent_phone' => '0500', 'status' => 'active']);

        Attendance::create(['group_id' => $group->id, 'student_id' => $student->id, 'coach_id' => $coach->id, 'attendance_date' => today(), 'status' => 'present']);
        Attendance::create(['group_id' => $group->id, 'student_id' => $student->id, 'coach_id' => $coach->id, 'attendance_date' => today()->subDay(), 'status' => 'absent']);

        $response = $this->withSession(['admin_user_id' => $admin->id])
            ->get('/panel?tab=raporlar');

        $response->assertOk();
        $response->assertSee('Çoklu Kriterli Yoklama Raporu');
        $response->assertSee('Katılım Oranı');
    }

    public function test_attendance_report_filters_by_branch(): void
    {
        $admin = $this->makeSuperAdmin();
        $branchA = Branch::create(['name' => 'Branş A', 'slug' => 'brans-a', 'age_range' => '4-12', 'is_active' => true]);
        $branchB = Branch::create(['name' => 'Branş B', 'slug' => 'brans-b', 'age_range' => '4-12', 'is_active' => true]);
        $coach = User::create([
            'name' => 'Antrenör B', 'email' => 'antrenorb@test.local', 'phone' => '05321112233',
            'role' => 'coach', 'compensation_type' => 'none', 'password' => Hash::make('x'), 'is_active' => true,
        ]);
        $groupA = Group::create(['branch_id' => $branchA->id, 'coach_id' => $coach->id, 'name' => 'Grup A', 'capacity' => 10, 'is_active' => true]);
        $groupB = Group::create(['branch_id' => $branchB->id, 'coach_id' => $coach->id, 'name' => 'Grup B', 'capacity' => 10, 'is_active' => true]);
        $studentA = Student::create(['full_name' => 'Öğrenci A', 'branch_id' => $branchA->id, 'group_id' => $groupA->id, 'parent_name' => 'Veli A', 'parent_phone' => '0500', 'status' => 'active']);
        $studentB = Student::create(['full_name' => 'Öğrenci B', 'branch_id' => $branchB->id, 'group_id' => $groupB->id, 'parent_name' => 'Veli B', 'parent_phone' => '0501', 'status' => 'active']);

        Attendance::create(['group_id' => $groupA->id, 'student_id' => $studentA->id, 'coach_id' => $coach->id, 'attendance_date' => today(), 'status' => 'present']);
        Attendance::create(['group_id' => $groupB->id, 'student_id' => $studentB->id, 'coach_id' => $coach->id, 'attendance_date' => today(), 'status' => 'absent']);

        $response = $this->withSession(['admin_user_id' => $admin->id])
            ->get('/panel?' . http_build_query(['tab' => 'raporlar', 'att_branch_id' => $branchA->id]));

        $response->assertOk();
        // Branş A filtresiyle toplam yoklama 1 olmalı (yalnızca Grup A kaydı), Branş B dışarıda kalmalı.
        $response->assertSeeInOrder(['Toplam Yoklama Kaydı', '1']);
    }
}
