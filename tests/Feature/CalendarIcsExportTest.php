<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Group;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CalendarIcsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_calendar_url_returns_a_valid_weekly_recurring_ics_file(): void
    {
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-ics-' . uniqid(), 'age_range' => '4-12', 'is_active' => true]);
        $coach = User::create([
            'name' => 'Antrenör', 'email' => 'antrenor-ics-' . uniqid() . '@test.local', 'phone' => '0532' . random_int(1000000, 9999999),
            'role' => 'coach', 'compensation_type' => 'none', 'password' => Hash::make('x'), 'is_active' => true,
        ]);
        $group = Group::create(['branch_id' => $branch->id, 'coach_id' => $coach->id, 'name' => 'Takvim Grubu', 'capacity' => 10, 'is_active' => true]);
        Schedule::create(['group_id' => $group->id, 'day_of_week' => 'Çarşamba', 'start_time' => '17:30:00', 'end_time' => '18:30:00', 'area' => 'Ana Minder']);

        $student = Student::create(['full_name' => 'Takvim Öğrencisi', 'branch_id' => $branch->id, 'group_id' => $group->id, 'parent_name' => 'Veli', 'parent_phone' => '0500', 'status' => 'active']);

        $response = $this->get($student->calendarUrl());

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $response->assertSeeText('BEGIN:VCALENDAR', false);
        $response->assertSeeText('RRULE:FREQ=WEEKLY;BYDAY=WE', false);
        $response->assertSeeText('Takvim Grubu', false);

        // İmzasız/elle uydurulmuş bir URL reddedilmeli.
        $this->get('/takvim/' . $student->id)->assertForbidden();
    }
}
