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

class QrCheckinTest extends TestCase
{
    use RefreshDatabase;

    private function makeGroup(string $slugSuffix): Group
    {
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-qr-' . $slugSuffix, 'age_range' => '4-12', 'is_active' => true]);
        $coach = User::create([
            'name' => 'Antrenör', 'email' => 'antrenor-qr-' . $slugSuffix . '@test.local', 'phone' => '0532' . random_int(1000000, 9999999),
            'role' => 'coach', 'compensation_type' => 'none', 'password' => Hash::make('x'), 'is_active' => true,
        ]);

        return Group::create(['branch_id' => $branch->id, 'coach_id' => $coach->id, 'name' => 'QR Grubu ' . $slugSuffix, 'capacity' => 10, 'is_active' => true]);
    }

    public function test_logged_in_parent_can_submit_qr_checkin_for_their_own_student(): void
    {
        $group = $this->makeGroup('a');
        $student = Student::create([
            'full_name' => 'QR Öğrencisi', 'branch_id' => $group->branch_id, 'group_id' => $group->id,
            'parent_name' => 'QR Veli', 'parent_phone' => '5331110001', 'portal_pin' => '1111', 'status' => 'active',
        ]);

        // Telefon + PIN ile oturum aç (route GET /qr-yoklama).
        $this->get('/qr-yoklama?phone=5331110001&pin=1111')
            ->assertOk()
            ->assertSee('QR Öğrencisi')
            ->assertSee('Yoklama Bekliyor');

        $this->post(route('public.qr.checkin.submit'), [
            'student_id' => $student->id,
            'checkin_type' => 'present',
        ])->assertRedirect(route('public.qr.checkin', ['phone' => $student->parent_phone]));

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'group_id' => $group->id,
            'status' => 'present',
        ]);
    }

    public function test_parent_cannot_submit_checkin_for_a_student_that_is_not_theirs(): void
    {
        $groupA = $this->makeGroup('b1');
        $groupB = $this->makeGroup('b2');

        Student::create([
            'full_name' => 'Veli A Öğrencisi', 'branch_id' => $groupA->branch_id, 'group_id' => $groupA->id,
            'parent_name' => 'Veli A', 'parent_phone' => '5331110002', 'portal_pin' => '2222', 'status' => 'active',
        ]);
        $otherStudent = Student::create([
            'full_name' => 'Veli B Öğrencisi', 'branch_id' => $groupB->branch_id, 'group_id' => $groupB->id,
            'parent_name' => 'Veli B', 'parent_phone' => '5331110003', 'portal_pin' => '3333', 'status' => 'active',
        ]);

        // Veli A olarak oturum aç, ama Veli B'nin öğrencisinin ID'siyle yoklama göndermeye çalış.
        $this->get('/qr-yoklama?phone=5331110002&pin=2222')->assertOk();

        $this->post(route('public.qr.checkin.submit'), [
            'student_id' => $otherStudent->id,
            'checkin_type' => 'present',
        ])->assertRedirect(route('public.qr.checkin'));

        $this->assertDatabaseMissing('attendances', ['student_id' => $otherStudent->id]);
    }

    public function test_checkin_requires_an_active_phone_and_pin_session(): void
    {
        $group = $this->makeGroup('c');
        $student = Student::create([
            'full_name' => 'Oturumsuz Öğrenci', 'branch_id' => $group->branch_id, 'group_id' => $group->id,
            'parent_name' => 'Oturumsuz Veli', 'parent_phone' => '5331110004', 'portal_pin' => '4444', 'status' => 'active',
        ]);

        // Hiç giriş yapılmamış bir tarayıcıdan doğrudan POST denemesi.
        $this->post(route('public.qr.checkin.submit'), [
            'student_id' => $student->id,
            'checkin_type' => 'present',
        ])->assertRedirect(route('public.qr.checkin'));

        $this->assertDatabaseMissing('attendances', ['student_id' => $student->id]);
    }

    public function test_already_logged_in_parent_sees_a_clear_login_confirmation_instead_of_the_login_form(): void
    {
        $group = $this->makeGroup('d');
        Student::create([
            'full_name' => 'Tanınan Öğrenci', 'branch_id' => $group->branch_id, 'group_id' => $group->id,
            'parent_name' => 'Tanınan Veli', 'parent_phone' => '5331110005', 'portal_pin' => '5555', 'status' => 'active',
        ]);

        // Giriş yapılmamışken sabit "1. Adım" formu görünür, "Giriş Yapıldı" rozeti görünmez.
        $this->get('/qr-yoklama')
            ->assertOk()
            ->assertSee('1. Adım: Veli Telefon Numaranız')
            ->assertDontSee('Giriş Yapıldı');

        // Telefon+PIN ile giriş yapınca artık "Giriş Yapıldı" rozeti görünür, sabit form görünmez
        // (bu, sitenin sahibinin bildirdiği "zaten içerideyken yine giriş istiyor" sorununu çözer).
        $response = $this->get('/qr-yoklama?phone=5331110005&pin=5555');
        $response->assertOk()
            ->assertSee('Giriş Yapıldı')
            ->assertSee('Tanınan Veli')
            ->assertDontSee('1. Adım: Veli Telefon Numaranız');
    }

    public function test_cikis_link_clears_the_qr_checkin_session(): void
    {
        $group = $this->makeGroup('e');
        Student::create([
            'full_name' => 'Çıkış Testi Öğrenci', 'branch_id' => $group->branch_id, 'group_id' => $group->id,
            'parent_name' => 'Çıkış Testi Veli', 'parent_phone' => '5331110006', 'portal_pin' => '6666', 'status' => 'active',
        ]);

        $this->withSession(['logged_parent_phone' => '5331110006', 'logged_parent_pin' => '6666'])
            ->get('/qr-yoklama?cikis=1')
            ->assertRedirect(route('public.qr.checkin'));

        $this->assertNull(session('logged_parent_phone'));

        $this->get('/qr-yoklama')->assertOk()->assertDontSee('Giriş Yapıldı');
    }
}
