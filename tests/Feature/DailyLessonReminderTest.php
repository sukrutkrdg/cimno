<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Group;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DailyLessonReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_task_queues_todays_lesson_reminder_for_both_guardians_without_duplicating(): void
    {
        $turkishDays = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
        $todayName = $turkishDays[now()->dayOfWeekIso];

        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-hatirlatma', 'age_range' => '4-12', 'is_active' => true]);
        $coach = User::create([
            'name' => 'Antrenör Hatırlatma', 'email' => 'antrenor-hatirlatma@test.local', 'phone' => '05321230000',
            'role' => 'coach', 'compensation_type' => 'none', 'password' => Hash::make('x'), 'is_active' => true,
        ]);
        $group = Group::create(['branch_id' => $branch->id, 'coach_id' => $coach->id, 'name' => 'Hatırlatma Grubu', 'capacity' => 10, 'is_active' => true]);
        Schedule::create(['group_id' => $group->id, 'day_of_week' => $todayName, 'start_time' => '14:00:00', 'end_time' => '15:00:00', 'area' => 'Ana Minder']);

        $student = Student::create([
            'full_name' => 'Hatırlatma Öğrencisi',
            'branch_id' => $branch->id,
            'group_id' => $group->id,
            'parent_name' => 'Anne Veli',
            'parent_phone' => '05330001111',
            'secondary_parent_name' => 'Baba Veli',
            'secondary_parent_phone' => '05440002222',
            'portal_pin' => '1234',
            'status' => 'active',
        ]);

        WhatsappTemplate::create([
            'shortcut' => '/bugun_ders_test',
            'title' => 'Bugün Dersiniz Var Hatırlatması',
            'trigger_event' => 'lesson_today',
            'category' => 'utility',
            'days_offset' => 0,
            'send_time' => '09:00:00',
            'message_template' => 'Merhaba {veli_adi}, {ogrenci_adi} için {grup_adi} dersi bugün {saat}.',
            'is_active' => true,
        ]);

        Artisan::call('enerjik:daily-tasks');

        $this->assertSame(2, WhatsappMessage::where('student_id', $student->id)->count());

        $primaryMsg = WhatsappMessage::where('student_id', $student->id)->where('recipient_phone', '05330001111')->first();
        $secondaryMsg = WhatsappMessage::where('student_id', $student->id)->where('recipient_phone', '05440002222')->first();

        $this->assertNotNull($primaryMsg);
        $this->assertNotNull($secondaryMsg);
        $this->assertStringContainsString('14:00-15:00', $primaryMsg->message_text);
        $this->assertStringContainsString('Baba Veli', $secondaryMsg->message_text);

        // Cron aynı gün tekrar tetiklenirse mükerrer kuyruklama olmamalı.
        Artisan::call('enerjik:daily-tasks');
        $this->assertSame(2, WhatsappMessage::where('student_id', $student->id)->count());
    }
}
