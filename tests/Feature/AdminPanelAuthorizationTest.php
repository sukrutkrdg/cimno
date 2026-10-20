<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Group;
use App\Models\Payment;
use App\Models\CashAccount;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPanelAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function makeStaff(string $role, array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Test ' . $role,
            'email' => strtolower($role) . '@test.local',
            'phone' => '05' . str_pad((string) random_int(0, 99999999), 9, '0', STR_PAD_LEFT),
            'role' => $role,
            'compensation_type' => 'none',
            'password' => Hash::make('gercek-sifre-123'),
            'is_active' => true,
            'permissions' => $role === 'coach' ? ['only_own_groups' => true, 'take_attendance' => true] : [],
        ], $overrides));
    }

    public function test_guest_is_redirected_away_from_panel(): void
    {
        $this->get('/panel')->assertRedirect(route('admin.login'));
    }

    public function test_login_rejects_the_old_hardcoded_backdoor_password(): void
    {
        $this->makeStaff('super_admin', ['email' => 'owner@test.local']);

        $response = $this->post('/panel/giris', [
            'identity' => 'owner@test.local',
            'password' => 'enerjik2026',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_login_succeeds_with_the_real_password(): void
    {
        $this->makeStaff('super_admin', ['email' => 'owner@test.local']);

        $response = $this->post('/panel/giris', [
            'identity' => 'owner@test.local',
            'password' => 'gercek-sifre-123',
        ]);

        $response->assertRedirect(route('admin.index'));
    }

    public function test_secretary_cannot_reach_super_admin_only_routes(): void
    {
        $secretary = $this->makeStaff('secretary');

        $response = $this->withSession(['admin_user_id' => $secretary->id])
            ->get('/panel/ayarlar/yedek-indir');

        $response->assertRedirect(route('admin.index'));
    }

    public function test_super_admin_can_reach_super_admin_only_routes(): void
    {
        $admin = $this->makeStaff('super_admin');

        $response = $this->withSession(['admin_user_id' => $admin->id])
            ->get('/panel/ayarlar/yedek-indir');

        $response->assertOk();
    }

    public function test_coach_cannot_edit_another_coachs_group(): void
    {
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans', 'age_range' => '4-12', 'is_active' => true]);
        $coachA = $this->makeStaff('coach', ['email' => 'coach.a@test.local']);
        $coachB = $this->makeStaff('coach', ['email' => 'coach.b@test.local']);
        $groupOfB = Group::create(['branch_id' => $branch->id, 'coach_id' => $coachB->id, 'name' => 'B Grubu', 'capacity' => 10, 'is_active' => true]);

        $response = $this->withSession(['admin_user_id' => $coachA->id])
            ->post("/panel/gruplar/{$groupOfB->id}/guncelle", ['name' => 'Ele Gecirildi']);

        $response->assertRedirect(route('admin.index'));
        $this->assertSame('B Grubu', $groupOfB->refresh()->name);
    }

    public function test_coach_can_edit_their_own_group(): void
    {
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-2', 'age_range' => '4-12', 'is_active' => true]);
        $coach = $this->makeStaff('coach');
        $group = Group::create(['branch_id' => $branch->id, 'coach_id' => $coach->id, 'name' => 'Eski Ad', 'capacity' => 10, 'is_active' => true]);

        $response = $this->withSession(['admin_user_id' => $coach->id])
            ->post("/panel/gruplar/{$group->id}/guncelle", ['name' => 'Yeni Ad']);

        $response->assertRedirect(route('admin.index', ['tab' => 'gruplar']));
        $this->assertSame('Yeni Ad', $group->refresh()->name);
    }

    public function test_receipt_link_requires_a_valid_signature(): void
    {
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-3', 'age_range' => '4-12', 'is_active' => true]);
        $student = Student::create(['full_name' => 'Test Öğrenci', 'branch_id' => $branch->id, 'parent_name' => 'Veli', 'parent_phone' => '0500', 'status' => 'active']);
        $invoice = Invoice::create(['student_id' => $student->id, 'period_label' => 'Test', 'gross_amount' => 100, 'net_amount' => 100, 'paid_amount' => 100, 'remaining_amount' => 0, 'due_date' => now(), 'status' => 'paid']);
        $account = CashAccount::create(['name' => 'Kasa', 'type' => 'cash', 'balance' => 0]);
        $payment = Payment::create(['receipt_no' => 'MKB-TEST-0001', 'invoice_id' => $invoice->id, 'student_id' => $student->id, 'cash_account_id' => $account->id, 'amount' => 100, 'payment_method' => 'cash', 'payment_date' => now()]);

        $this->get("/makbuz/{$payment->id}")->assertForbidden();
        $this->get($payment->receiptUrl())->assertOk();
    }
}
