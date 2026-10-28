<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\Group;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OctoberBatchFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function makeAdmin(): User
    {
        return User::create([
            'name' => 'Test Admin',
            'email' => 'admin-oct-batch@test.local',
            'phone' => '5320000010',
            'role' => 'super_admin',
            'compensation_type' => 'none',
            'password' => Hash::make('gercek-sifre-123'),
            'is_active' => true,
            'permissions' => [],
        ]);
    }

    public function test_admin_can_correct_a_past_absence_to_excused_for_a_makeup_credit(): void
    {
        $admin = $this->makeAdmin();
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-att-fix', 'age_range' => '4-12', 'is_active' => true]);
        $group = Group::create(['branch_id' => $branch->id, 'name' => 'Grup', 'capacity' => 10, 'is_active' => true]);
        $student = Student::create([
            'full_name' => 'Yoklama Düzeltme Öğrenci', 'branch_id' => $branch->id, 'group_id' => $group->id,
            'parent_name' => 'Veli', 'parent_phone' => '5330000010', 'status' => 'active',
        ]);
        $attendance = Attendance::create([
            'group_id' => $group->id, 'student_id' => $student->id, 'attendance_date' => today()->subDay(),
            'status' => 'absent',
        ]);

        $this->withSession(['admin_user_id' => $admin->id])
            ->post(route('admin.attendance.update', $attendance->id), ['status' => 'excused'])
            ->assertRedirect(route('admin.index', ['tab' => 'yoklama']));

        $this->assertSame('excused', $attendance->refresh()->status);
        $this->assertSame(1, $student->refresh()->makeup_credits);
    }

    public function test_homepage_does_not_leak_group_headcounts_or_registration_status(): void
    {
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-kontenjan', 'age_range' => '4-12', 'is_active' => true]);
        $group = Group::create(['branch_id' => $branch->id, 'name' => 'Gizli Kontenjan Grubu', 'capacity' => 10, 'is_active' => true]);
        Student::create(['full_name' => 'Dolu Öğrenci', 'branch_id' => $branch->id, 'group_id' => $group->id, 'parent_name' => 'Veli', 'parent_phone' => '5330000011', 'status' => 'active']);

        $response = $this->get('/');
        $response->assertOk()
            ->assertSee('Gizli Kontenjan Grubu')
            ->assertDontSee('Kayıt Açık')
            ->assertDontSee('Kontenjan Dolu')
            ->assertDontSee('Son ' . '1' . ' Yer');
    }

    public function test_trial_lead_whatsapp_confirmation_says_spor_salonu_not_spor_kulubu(): void
    {
        $this->post('/on-kayit', [
            'parent_name' => 'Test Veli',
            'parent_phone' => '0533 222 33 55',
            'student_name' => 'Test Çocuk',
            'student_age' => 6,
            'kvkk_approved' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('whatsapp_messages', ['recipient_name' => 'Test Veli (Aday - Test Çocuk)']);
        $msg = \App\Models\WhatsappMessage::where('recipient_name', 'Test Veli (Aday - Test Çocuk)')->first();
        $this->assertStringContainsString('Enerjik Cimnastik Spor Salonu', $msg->message_text);
        $this->assertStringNotContainsString('Spor Kulübü', $msg->message_text);
    }

    public function test_student_registration_accepts_registration_date_and_billing_day(): void
    {
        $admin = $this->makeAdmin();
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-kayit-tarihi', 'age_range' => '4-12', 'is_active' => true]);
        $group = Group::create(['branch_id' => $branch->id, 'name' => 'Grup', 'capacity' => 10, 'is_active' => true]);
        $package = Package::create(['name' => 'Paket', 'price' => 100, 'lessons_count' => 8, 'is_active' => true, 'show_on_site' => true]);

        $this->withSession(['admin_user_id' => $admin->id])
            ->post('/panel/ogrenciler', [
                'full_name' => 'Tarihli Öğrenci',
                'gender' => 'Belirtilmedi',
                'branch_id' => $branch->id,
                'group_id' => $group->id,
                'package_id' => $package->id,
                'parent_name' => 'Veli',
                'parent_phone' => '5330000012',
                'registration_date' => '2026-09-01',
                'billing_day' => 15,
            ])->assertRedirect();

        $student = Student::where('full_name', 'Tarihli Öğrenci')->firstOrFail();
        $this->assertSame('2026-09-01', $student->registration_date->toDateString());
        $this->assertSame(15, $student->billing_day);
    }

    public function test_monthly_invoice_generation_uses_each_students_billing_day(): void
    {
        $admin = $this->makeAdmin();
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-odeme-gunu', 'age_range' => '4-12', 'is_active' => true]);
        $package = Package::create(['name' => 'Paket', 'price' => 1000, 'lessons_count' => 8, 'is_active' => true, 'show_on_site' => true]);
        $student = Student::create([
            'full_name' => 'Ödeme Günü Öğrenci', 'branch_id' => $branch->id, 'package_id' => $package->id,
            'parent_name' => 'Veli', 'parent_phone' => '5330000013', 'status' => 'active', 'billing_day' => 20,
        ]);

        $this->withSession(['admin_user_id' => $admin->id])
            ->post(route('admin.invoices.generate'), ['period_label' => 'Test Dönemi'])
            ->assertRedirect();

        $invoice = Invoice::where('student_id', $student->id)->where('period_label', 'Test Dönemi')->firstOrFail();
        $expectedDueDate = now()->startOfMonth()->addMonth()->addDays(19)->toDateString();
        $this->assertSame($expectedDueDate, $invoice->due_date->toDateString());
    }
}
