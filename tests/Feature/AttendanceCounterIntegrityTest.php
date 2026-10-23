<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use App\Models\WhatsappMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceCounterIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function makeSuperAdmin(): User
    {
        return User::create([
            'name' => 'Test Yönetici', 'email' => 'yonetici-att@test.local', 'phone' => '05321234567',
            'role' => 'super_admin', 'compensation_type' => 'none', 'password' => Hash::make('x'), 'is_active' => true,
            'permissions' => ['all' => true],
        ]);
    }

    protected function makeGroupWithStudent(): array
    {
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-att-' . uniqid(), 'age_range' => '4-12', 'is_active' => true]);
        $coach = User::create([
            'name' => 'Antrenör Test', 'email' => 'antrenor-att-' . uniqid() . '@test.local', 'phone' => '0532' . random_int(1000000, 9999999),
            'role' => 'coach', 'compensation_type' => 'none', 'password' => Hash::make('x'), 'is_active' => true,
        ]);
        $group = Group::create(['branch_id' => $branch->id, 'coach_id' => $coach->id, 'name' => 'Test Grubu', 'capacity' => 10, 'is_active' => true]);
        $student = Student::create(['full_name' => 'Test Öğrenci', 'branch_id' => $branch->id, 'group_id' => $group->id, 'parent_name' => 'Veli', 'parent_phone' => '0500', 'status' => 'active']);

        return [$group, $student];
    }

    protected function submitAttendance(User $admin, Group $group, Student $student, string $status, string $date): void
    {
        $this->withSession(['admin_user_id' => $admin->id])->post(route('admin.attendance.store'), [
            'group_id' => $group->id,
            'attendance_date' => $date,
            'statuses' => [$student->id => $status],
        ])->assertRedirect();
    }

    public function test_two_consecutive_absences_trigger_exactly_one_warning_even_if_resubmitted(): void
    {
        $admin = $this->makeSuperAdmin();
        [$group, $student] = $this->makeGroupWithStudent();

        $this->submitAttendance($admin, $group, $student, 'absent', '2026-01-05');
        $this->submitAttendance($admin, $group, $student, 'absent', '2026-01-12');

        $student->refresh();
        $this->assertSame(2, $student->consecutive_absences);
        $this->assertSame(1, WhatsappMessage::where('student_id', $student->id)->count());

        // Aynı yoklamayı tekrar kaydetmek (ör. form yanlışlıkla iki kez gönderilirse)
        // sayaç veya uyarı mesajı sayısını ARTIRMAMALI.
        $this->submitAttendance($admin, $group, $student, 'absent', '2026-01-12');
        $student->refresh();
        $this->assertSame(2, $student->consecutive_absences);
        $this->assertSame(1, WhatsappMessage::where('student_id', $student->id)->count());
    }

    public function test_correcting_absent_to_present_resets_the_streak_instead_of_leaving_it_stuck(): void
    {
        $admin = $this->makeSuperAdmin();
        [$group, $student] = $this->makeGroupWithStudent();

        $this->submitAttendance($admin, $group, $student, 'absent', '2026-01-05');
        $this->submitAttendance($admin, $group, $student, 'absent', '2026-01-12');
        $this->assertSame(2, $student->refresh()->consecutive_absences);

        // Antrenör yanlış girmiş, aslında öğrenci gelmiş — düzeltir.
        $this->submitAttendance($admin, $group, $student, 'present', '2026-01-12');
        $this->assertSame(0, $student->refresh()->consecutive_absences);
    }

    public function test_excused_grants_and_makeup_spends_a_credit_consistently_after_corrections(): void
    {
        $admin = $this->makeSuperAdmin();
        [$group, $student] = $this->makeGroupWithStudent();

        $this->submitAttendance($admin, $group, $student, 'excused', '2026-01-05');
        $this->assertSame(1, $student->refresh()->makeup_credits);

        $this->submitAttendance($admin, $group, $student, 'makeup', '2026-01-12');
        $this->assertSame(0, $student->refresh()->makeup_credits);

        // Telafi hakkının kaynağı olan "raporlu" kaydı sonradan "gelmedi"ye
        // düzeltilirse, kullanılmamış/kullanılmış hak sayısı da gerçek duruma
        // göre yeniden hesaplanmalı — negatife düşmemeli.
        $this->submitAttendance($admin, $group, $student, 'absent', '2026-01-05');
        $this->assertSame(0, $student->refresh()->makeup_credits);
    }

    public function test_deleting_an_absence_record_reverts_the_streak(): void
    {
        $admin = $this->makeSuperAdmin();
        [$group, $student] = $this->makeGroupWithStudent();

        $this->submitAttendance($admin, $group, $student, 'absent', '2026-01-05');
        $this->submitAttendance($admin, $group, $student, 'absent', '2026-01-12');
        $this->assertSame(2, $student->refresh()->consecutive_absences);

        $latest = $student->attendances()->whereDate('attendance_date', '2026-01-12')->first();
        $this->withSession(['admin_user_id' => $admin->id])
            ->post(route('admin.attendance.destroy', $latest->id));

        $this->assertSame(1, $student->refresh()->consecutive_absences);
    }
}
