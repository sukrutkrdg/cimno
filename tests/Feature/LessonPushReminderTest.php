<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Group;
use App\Models\PushSubscription;
use App\Models\Schedule;
use App\Models\SiteSetting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Minishlink\WebPush\VAPID;
use Tests\TestCase;

class LessonPushReminderTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{publicKey: string, privateKey: string} Gerçek biçimde ama test amaçlı, tek seferlik bir anahtar çifti. */
    protected function fakeVapidKeys(): array
    {
        return VAPID::createVapidKeys();
    }

    public function test_command_skips_cleanly_without_throwing_when_vapid_keys_are_not_configured(): void
    {
        config(['services.vapid.public_key' => null, 'services.vapid.private_key' => null]);

        $exitCode = Artisan::call('enerjik:ders-bildirimi-gonder');

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('VAPID anahtarları tanımlı değil', Artisan::output());
    }

    public function test_command_does_not_queue_anything_for_a_lesson_outside_the_reminder_window(): void
    {
        $keys = $this->fakeVapidKeys();
        config(['services.vapid.public_key' => $keys['publicKey'], 'services.vapid.private_key' => $keys['privateKey']]);

        $turkishDays = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
        $todayName = $turkishDays[now()->dayOfWeekIso];

        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-push-' . uniqid(), 'age_range' => '4-12', 'is_active' => true]);
        $coach = User::create([
            'name' => 'Antrenör', 'email' => 'antrenor-push-' . uniqid() . '@test.local', 'phone' => '0532' . random_int(1000000, 9999999),
            'role' => 'coach', 'compensation_type' => 'none', 'password' => Hash::make('x'), 'is_active' => true,
        ]);
        $group = Group::create(['branch_id' => $branch->id, 'coach_id' => $coach->id, 'name' => 'Push Grubu', 'capacity' => 10, 'is_active' => true]);
        // Pencerenin çok dışında (3 saat sonra) başlayan bir ders — bildirim tetiklenmemeli.
        Schedule::create(['group_id' => $group->id, 'day_of_week' => $todayName, 'start_time' => now()->addHours(3)->format('H:i:s'), 'end_time' => now()->addHours(4)->format('H:i:s'), 'area' => 'Ana Minder']);
        $student = Student::create(['full_name' => 'Push Öğrenci', 'branch_id' => $branch->id, 'group_id' => $group->id, 'parent_name' => 'Veli', 'parent_phone' => '5330001122', 'status' => 'active']);
        PushSubscription::create(['parent_phone' => '5330001122', 'endpoint' => 'https://fcm.googleapis.com/fcm/send/out-of-window', 'public_key' => 'k', 'auth_token' => 'a']);

        Artisan::call('enerjik:ders-bildirimi-gonder');

        $this->assertStringContainsString('başlayacak ders bulunamadı', Artisan::output());
    }

    public function test_dedup_cache_key_prevents_sending_twice_for_the_same_lesson_and_day(): void
    {
        $keys = $this->fakeVapidKeys();
        config(['services.vapid.public_key' => $keys['publicKey'], 'services.vapid.private_key' => $keys['privateKey']]);

        $turkishDays = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
        $todayName = $turkishDays[now()->dayOfWeekIso];

        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-push2-' . uniqid(), 'age_range' => '4-12', 'is_active' => true]);
        $coach = User::create([
            'name' => 'Antrenör', 'email' => 'antrenor-push2-' . uniqid() . '@test.local', 'phone' => '0532' . random_int(1000000, 9999999),
            'role' => 'coach', 'compensation_type' => 'none', 'password' => Hash::make('x'), 'is_active' => true,
        ]);
        $group = Group::create(['branch_id' => $branch->id, 'coach_id' => $coach->id, 'name' => 'Push Grubu 2', 'capacity' => 10, 'is_active' => true]);
        // Varsayılan hatırlatma parametresi 60 dk (1 saat kala), pencere
        // genişliği 30 dk (iki tetikleme aralığı, cron atlamalarına karşı
        // toleranslı) — bu yüzden ders +52 dk'ya ayarlanır (30-60 dk aralığının
        // içinde), eski sabit 20-35 dk penceresindeki +28 dk yerine.
        $schedule = Schedule::create(['group_id' => $group->id, 'day_of_week' => $todayName, 'start_time' => now()->addMinutes(52)->format('H:i:s'), 'end_time' => now()->addMinutes(82)->format('H:i:s'), 'area' => 'Ana Minder']);

        $dedupKey = 'push-ders-' . $schedule->id . '-' . today()->toDateString();
        Cache::put($dedupKey, true, now()->addHours(20));

        Artisan::call('enerjik:ders-bildirimi-gonder');

        // Zaten "gönderilmiş" olarak işaretlenmiş olduğundan, komut bu ders için
        // tekrar işlem yapmamalı (0 bildirim kuyruklanmalı).
        $this->assertStringContainsString('0 push bildirimi gönderildi', Artisan::output());
    }

    public function test_reminder_window_follows_the_admin_configurable_minutes_before_setting(): void
    {
        $keys = $this->fakeVapidKeys();
        config(['services.vapid.public_key' => $keys['publicKey'], 'services.vapid.private_key' => $keys['privateKey']]);
        // Yönetici "30 dakika kala" ayarlamış — varsayılan 60 yerine bu kullanılmalı.
        SiteSetting::create(['key' => 'lesson_reminder_minutes_before', 'value' => '30']);

        $turkishDays = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
        $todayName = $turkishDays[now()->dayOfWeekIso];

        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-push3-' . uniqid(), 'age_range' => '4-12', 'is_active' => true]);
        $coach = User::create([
            'name' => 'Antrenör', 'email' => 'antrenor-push3-' . uniqid() . '@test.local', 'phone' => '0532' . random_int(1000000, 9999999),
            'role' => 'coach', 'compensation_type' => 'none', 'password' => Hash::make('x'), 'is_active' => true,
        ]);
        $group = Group::create(['branch_id' => $branch->id, 'coach_id' => $coach->id, 'name' => 'Push Grubu 3', 'capacity' => 10, 'is_active' => true]);
        // 30 dk ayarıyla pencere 0-30 dk arası olur; +22 dk bu aralıkta, eski
        // varsayılan (30-60 dk) ile YAKALANMAZDI — ayarın gerçekten okunduğunu kanıtlar.
        Schedule::create(['group_id' => $group->id, 'day_of_week' => $todayName, 'start_time' => now()->addMinutes(22)->format('H:i:s'), 'end_time' => now()->addMinutes(52)->format('H:i:s'), 'area' => 'Ana Minder']);
        $student = Student::create(['full_name' => 'Push Öğrenci 3', 'branch_id' => $branch->id, 'group_id' => $group->id, 'parent_name' => 'Veli', 'parent_phone' => '5330001133', 'status' => 'active']);
        PushSubscription::create(['parent_phone' => '5330001133', 'endpoint' => 'https://fcm.googleapis.com/fcm/send/configurable-window', 'public_key' => 'k', 'auth_token' => 'a']);

        Artisan::call('enerjik:ders-bildirimi-gonder');

        $this->assertStringContainsString('1 push bildirimi gönderildi', Artisan::output());
    }
}
