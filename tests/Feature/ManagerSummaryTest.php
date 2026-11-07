<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ManagerSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function makeAdmin(): User
    {
        return User::create([
            'name' => 'Turgay Bakangöz', 'email' => 'admin-ozet@test.local', 'phone' => '5320000004',
            'role' => 'super_admin', 'compensation_type' => 'none',
            'password' => Hash::make('gercek-sifre-123'), 'is_active' => true, 'permissions' => [],
        ]);
    }

    public function test_logged_in_staff_can_see_the_manager_summary_screen(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->withSession(['admin_user_id' => $admin->id])->get(route('admin.summary'));

        $response->assertOk()
            ->assertSee('Yönetici Özeti', false)
            ->assertSee('Bugünkü Dersler', false)
            ->assertSee('Bugünkü Yoklama', false);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.summary'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_todays_schedule_for_the_current_weekday_is_listed(): void
    {
        $admin = $this->makeAdmin();
        $branch = \App\Models\Branch::create(['name' => 'Artistik Cimnastik', 'slug' => 'artistik-cimnastik', 'age_range' => '4-12 Yaş', 'level' => 'Başlangıç']);
        $group = Group::create(['branch_id' => $branch->id, 'name' => 'Test Grubu', 'capacity' => 10, 'is_active' => true]);
        $turkishDays = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
        $today = $turkishDays[now()->dayOfWeekIso];
        Schedule::create([
            'group_id' => $group->id, 'day_of_week' => $today,
            'start_time' => '17:00', 'end_time' => '18:00', 'area' => 'Ana Minder',
        ]);

        $response = $this->withSession(['admin_user_id' => $admin->id])->get(route('admin.summary'));

        $response->assertOk()->assertSee('Test Grubu');
    }
}
