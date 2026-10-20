<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\DevelopmentRecord;
use App\Models\DiscountRule;
use App\Models\Event;
use App\Models\FinancialTransaction;
use App\Models\Group;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\SiteSetting;
use App\Models\Student;
use App\Models\User;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;

trait ManagesSystem
{
    // MODÜL 16: Veritabanı Yedeğini İndirme (Kabul Kriteri #8 - Yalnız Süper Yönetici)
    public function downloadDatabaseBackup()
    {
        // Yetki kontrolü route middleware'inde (admin.permission:role:super_admin) merkezi olarak yapılır.
        $tables = [
            'users' => User::all(),
            'branches' => Branch::all(),
            'packages' => Package::all(),
            'discount_rules' => DiscountRule::all(),
            'groups' => Group::all(),
            'schedules' => Schedule::all(),
            'leads' => Lead::all(),
            'students' => Student::all(),
            'attendances' => Attendance::all(),
            'cash_accounts' => CashAccount::all(),
            'invoices' => Invoice::all(),
            'payments' => Payment::all(),
            'financial_transactions' => FinancialTransaction::all(),
            'development_records' => DevelopmentRecord::all(),
            'events' => Event::all(),
            'whatsapp_templates' => WhatsappTemplate::all(),
            'whatsapp_messages' => WhatsappMessage::all(),
            'site_settings' => SiteSetting::all(),
            'audit_logs' => AuditLog::all(),
        ];

        $backupPayload = [
            'club' => 'Enerjik Cimnastik Spor Kulübü',
            'generated_at' => now()->toIso8601String(),
            'database' => config('database.connections.mysql.database'),
            'tables' => $tables,
        ];

        AuditLog::record('yedek_indirildi', 'Tam veritabanı yedeği yönetici tarafından indirildi.');

        $filename = 'enerjik-cimnastik-tam-yedek-' . now()->format('Y-m-d-His') . '.json';
        return response()->streamDownload(function () use ($backupPayload) {
            echo json_encode($backupPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, ['Content-Type' => 'application/json; charset=UTF-8']);
    }
}
