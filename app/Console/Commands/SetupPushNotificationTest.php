<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Group;
use App\Models\Schedule;
use App\Models\Student;
use App\Support\Phone;
use Illuminate\Console\Command;

class SetupPushNotificationTest extends Command
{
    /**
     * Tek seferlik, elle tetiklenecek bir bakım komutu: salon sahibi admin paneli
     * şifresini artık bilmiyor (sekreterliğe devretmiş), bu yüzden "ders saati
     * yaklaşıyor" Web Push bildirimini canlıda test edebilmesi için gereken
     * test branş/grup/ders programı/öğrenci kaydını panele girmeden, doğrudan
     * oluşturur. Tekrar çalıştırılırsa aynı test grubunu/öğrencisini günceller
     * (yeni kopya oluşturmaz), böylece saat ayarını yeniden denemek güvenlidir.
     */
    protected $signature = 'enerjik:test-bildirim-kurulum {phone : Veli telefon numarası (başında 0 ile veya boşluklu, her biçim olur)} {--dakika=50 : Ders ne kadar dakika sonra başlasın}';

    protected $description = 'Web Push "ders saati yaklaşıyor" bildirimini canlıda test etmek için geçici bir branş/grup/ders/öğrenci kaydı oluşturur veya günceller';

    public function handle(): int
    {
        $phone = Phone::normalize($this->argument('phone'));
        if ($phone === null) {
            $this->error('Geçersiz telefon numarası.');
            return self::FAILURE;
        }

        $minutesAhead = max(5, (int) $this->option('dakika'));

        $branch = Branch::firstOrCreate(
            ['slug' => 'test-bildirim-branch'],
            ['name' => 'TEST Bildirim', 'age_range' => '3-99', 'is_active' => true]
        );

        $group = Group::firstOrCreate(
            ['branch_id' => $branch->id, 'name' => 'TEST Bildirim Grubu'],
            ['capacity' => 20, 'is_active' => true]
        );

        $turkishDays = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
        $todayName = $turkishDays[now()->dayOfWeekIso];
        $startsAt = now()->addMinutes($minutesAhead);

        // Önceki test çalıştırmalarından kalan ders saatlerini temizle; bu test
        // grubunda her zaman TEK bir güncel ders aralığı olsun.
        Schedule::where('group_id', $group->id)->delete();
        $schedule = Schedule::create([
            'group_id' => $group->id,
            'day_of_week' => $todayName,
            'start_time' => $startsAt->format('H:i:s'),
            'end_time' => $startsAt->copy()->addMinutes(30)->format('H:i:s'),
            'area' => 'Test Alanı',
        ]);

        $student = Student::firstOrNew(['full_name' => 'TEST Bildirim Denemesi', 'parent_phone' => $phone]);
        $student->fill([
            'branch_id' => $branch->id,
            'group_id' => $group->id,
            'parent_name' => 'Test Veli',
            'parent_phone' => $phone,
            'portal_pin' => $student->portal_pin ?: Student::portalPinFor($phone),
            'status' => 'active',
            'health_notes' => $student->health_notes ?: 'Test kaydı.',
            'medical_report_expires_at' => $student->medical_report_expires_at ?: now()->addYear()->toDateString(),
            'registration_date' => $student->registration_date ?: now()->toDateString(),
        ]);
        $student->save();

        $this->info('Test kurulumu tamam.');
        $this->info("Telefon: {$phone}");
        $this->info("PIN: {$student->portal_pin}");
        $this->info("Ders: {$todayName} {$startsAt->format('H:i')} - " . $startsAt->copy()->addMinutes(30)->format('H:i') . " (şu andan {$minutesAhead} dakika sonra)");
        $this->info("Öğrenci ID: {$student->id} / Grup ID: {$group->id} / Ders Programı ID: {$schedule->id}");

        return self::SUCCESS;
    }
}
