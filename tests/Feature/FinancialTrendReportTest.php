<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\FinancialTransaction;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FinancialTrendReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_raporlar_tab_shows_monthly_income_and_expense_trend(): void
    {
        $admin = User::create([
            'name' => 'Yönetici', 'email' => 'yonetici-trend@test.local', 'phone' => '05321234567',
            'role' => 'super_admin', 'compensation_type' => 'none', 'password' => Hash::make('x'), 'is_active' => true,
            'permissions' => ['all' => true],
        ]);

        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-trend', 'age_range' => '4-12', 'is_active' => true]);
        $student = Student::create(['full_name' => 'Trend Öğrenci', 'branch_id' => $branch->id, 'parent_name' => 'Veli', 'parent_phone' => '0500', 'status' => 'active']);
        $account = CashAccount::create(['name' => 'Kasa', 'type' => 'cash', 'balance' => 0]);
        $invoice = Invoice::create(['student_id' => $student->id, 'period_label' => 'Test', 'gross_amount' => 1000, 'net_amount' => 1000, 'paid_amount' => 1000, 'remaining_amount' => 0, 'due_date' => now(), 'status' => 'paid']);

        Payment::createWithReceiptNo([
            'invoice_id' => $invoice->id, 'student_id' => $student->id, 'cash_account_id' => $account->id,
            'amount' => 1500, 'payment_method' => 'cash', 'payment_date' => now(),
        ]);

        FinancialTransaction::create([
            'direction' => 'expense', 'category' => 'Kira', 'cash_account_id' => $account->id,
            'title' => 'Aylık Kira', 'amount' => 400, 'transaction_date' => now(),
        ]);

        $response = $this->withSession(['admin_user_id' => $admin->id])->get('/panel?tab=raporlar');

        $response->assertOk();
        $response->assertSee('Aylık Gelir-Gider Trendi');
        // Bu ayın toplam geliri (1500) ve gideri (400) grafiğe geçen JSON verisinde yer almalı.
        $response->assertSeeText('"gelir":1500', false);
        $response->assertSeeText('"gider":400', false);
    }
}
