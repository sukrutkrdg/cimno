<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Group;
use App\Models\Lead;
use App\Models\Student;
use App\Models\User;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WhatsappTemplateWiringTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_task_sends_trial_reminder_the_day_before_using_the_editable_template(): void
    {
        $turkishDays = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
        $tomorrow = today()->addDay();
        $tomorrowName = $turkishDays[$tomorrow->dayOfWeekIso];

        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-trial-' . uniqid(), 'age_range' => '4-12', 'is_active' => true]);
        $coach = User::create([
            'name' => 'Antrenör', 'email' => 'antrenor-trial-' . uniqid() . '@test.local', 'phone' => '0532' . random_int(1000000, 9999999),
            'role' => 'coach', 'compensation_type' => 'none', 'password' => Hash::make('x'), 'is_active' => true,
        ]);
        $group = Group::create(['branch_id' => $branch->id, 'coach_id' => $coach->id, 'name' => 'Deneme Grubu', 'capacity' => 10, 'is_active' => true]);
        \App\Models\Schedule::create(['group_id' => $group->id, 'day_of_week' => $tomorrowName, 'start_time' => '17:00:00', 'end_time' => '18:00:00', 'area' => 'Ana Minder']);

        $lead = Lead::create([
            'parent_name' => 'Test Veli', 'parent_phone' => '05331112233', 'student_name' => 'Test Aday',
            'student_age' => 6, 'branch_id' => $branch->id, 'trial_group_id' => $group->id,
            'trial_date' => $tomorrow->toDateString(), 'status' => 'trial_scheduled', 'source' => 'website',
        ]);

        WhatsappTemplate::create([
            'shortcut' => '/deneme_test', 'title' => 'Deneme Hatırlatma', 'trigger_event' => 'trial_reminder',
            'category' => 'utility', 'days_offset' => -1, 'send_time' => '19:00:00', 'is_active' => true,
            'message_template' => 'Merhaba {veli_adi}, {ogrenci_adi} için yarın {tarih} saat {saat} deneme dersiniz var.',
        ]);

        Artisan::call('enerjik:daily-tasks');

        $msg = WhatsappMessage::where('recipient_phone', '05331112233')->first();
        $this->assertNotNull($msg);
        $this->assertStringContainsString('17:00-18:00', $msg->message_text);
        $this->assertStringContainsString($tomorrow->format('d.m.Y'), $msg->message_text);

        // Cron ikinci kez çalışırsa mükerrer kuyruklama olmamalı.
        Artisan::call('enerjik:daily-tasks');
        $this->assertSame(1, WhatsappMessage::where('recipient_phone', '05331112233')->count());
    }

    public function test_daily_task_sends_medical_report_reminder_using_the_editable_template(): void
    {
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-med-' . uniqid(), 'age_range' => '4-12', 'is_active' => true]);
        $student = Student::create([
            'full_name' => 'Sağlık Testi', 'branch_id' => $branch->id, 'parent_name' => 'Veli', 'parent_phone' => '05339998877',
            'status' => 'active', 'medical_report_expires_at' => today()->addDays(15)->toDateString(),
        ]);

        WhatsappTemplate::create([
            'shortcut' => '/rapor_test', 'title' => 'Sağlık Raporu', 'trigger_event' => 'medical_report_expiring',
            'category' => 'utility', 'days_offset' => -15, 'send_time' => '12:00:00', 'is_active' => true,
            'message_template' => '{veli_adi}, {ogrenci_adi} raporu {vade} tarihinde dolacak.',
        ]);

        Artisan::call('enerjik:daily-tasks');

        $msg = WhatsappMessage::where('student_id', $student->id)->first();
        $this->assertNotNull($msg);
        $this->assertStringContainsString(today()->addDays(15)->format('d.m.Y'), $msg->message_text);
    }

    public function test_absence_warning_uses_the_editable_template_text_when_active(): void
    {
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-abs-' . uniqid(), 'age_range' => '4-12', 'is_active' => true]);
        $coach = User::create([
            'name' => 'Antrenör', 'email' => 'antrenor-abs-' . uniqid() . '@test.local', 'phone' => '0532' . random_int(1000000, 9999999),
            'role' => 'coach', 'compensation_type' => 'none', 'password' => Hash::make('x'), 'is_active' => true,
        ]);
        $group = Group::create(['branch_id' => $branch->id, 'coach_id' => $coach->id, 'name' => 'Grup', 'capacity' => 10, 'is_active' => true]);
        $student = Student::create(['full_name' => 'Devamsız Öğrenci', 'branch_id' => $branch->id, 'group_id' => $group->id, 'parent_name' => 'Veli', 'parent_phone' => '0500', 'status' => 'active']);

        WhatsappTemplate::create([
            'shortcut' => '/devam_test', 'title' => 'Devamsızlık', 'trigger_event' => 'absence_warning',
            'category' => 'utility', 'days_offset' => 0, 'send_time' => '20:00:00', 'is_active' => true,
            'message_template' => 'ÖZEL METİN: {ogrenci_adi} {sayi} kez gelmedi.',
        ]);

        $admin = User::create([
            'name' => 'Yönetici', 'email' => 'yonetici-abs@test.local', 'phone' => '05320000000',
            'role' => 'super_admin', 'compensation_type' => 'none', 'password' => Hash::make('x'), 'is_active' => true,
            'permissions' => ['all' => true],
        ]);

        $this->withSession(['admin_user_id' => $admin->id])->post(route('admin.attendance.store'), [
            'group_id' => $group->id, 'attendance_date' => '2026-01-05', 'statuses' => [$student->id => 'absent'],
        ]);
        $this->withSession(['admin_user_id' => $admin->id])->post(route('admin.attendance.store'), [
            'group_id' => $group->id, 'attendance_date' => '2026-01-12', 'statuses' => [$student->id => 'absent'],
        ]);

        $msg = WhatsappMessage::where('student_id', $student->id)->first();
        $this->assertNotNull($msg);
        $this->assertStringContainsString('ÖZEL METİN: Devamsız Öğrenci 2 kez gelmedi.', $msg->message_text);
    }
}
