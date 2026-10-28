<?php

namespace App\Console\Commands;

use App\Models\PushSubscription;
use App\Models\Schedule as LessonSchedule;
use App\Models\SiteSetting;
use App\Support\Phone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class DiagnosePushNotification extends Command
{
    /**
     * Tek seferlik, elle tetiklenecek teşhis komutu: "ders saati yaklaşıyor" Web
     * Push bildirimi canlıda gelmediğinde, enerjik:ders-bildirimi-gonder komutunun
     * izlediği TÜM adımları (VAPID yapılandırması, aboneliğin var olup olmadığı,
     * zaman penceresi hesaplaması, dedup önbelleği) tek seferde raporlar.
     */
    protected $signature = 'enerjik:test-bildirim-teshis {phone : Veli telefon numarası}';

    protected $description = 'Web Push ders hatırlatmasının neden gelmediğini (VAPID, abonelik, zaman penceresi, dedup) teşhis eder';

    public function handle(): int
    {
        $phone = Phone::normalize($this->argument('phone'));
        if ($phone === null) {
            $this->error('Geçersiz telefon numarası.');
            return self::FAILURE;
        }

        $this->info("=== 1. VAPID Yapılandırması ===");
        $publicKey = config('services.vapid.public_key');
        $privateKey = config('services.vapid.private_key');
        $this->info($publicKey ? "Public key tanımlı (" . strlen($publicKey) . " karakter)." : 'Public key TANIMLI DEĞİL!');
        $this->info($privateKey ? "Private key tanımlı (" . strlen($privateKey) . " karakter)." : 'Private key TANIMLI DEĞİL!');

        $this->info("\n=== 2. Push Aboneliği (telefon: {$phone}) ===");
        $subs = PushSubscription::where('parent_phone', $phone)->get();
        if ($subs->isEmpty()) {
            $this->warn('Bu telefon için HİÇBİR push aboneliği bulunamadı. Veli /mobil sayfasında giriş yapıp "Bildirimler" butonuna basıp izin vermemiş olabilir.');
        } else {
            foreach ($subs as $sub) {
                $this->info("Abonelik #{$sub->id} — endpoint: " . substr($sub->endpoint, 0, 60) . "... — oluşturulma: {$sub->created_at}");
            }
        }

        $this->info("\n=== 3. Zaman Penceresi Hesabı (şu an: " . now()->format('H:i:s') . ") ===");
        $reminderMinutes = max(5, (int) (SiteSetting::where('key', 'lesson_reminder_minutes_before')->value('value') ?? 60));
        $windowStart = now()->addMinutes(max(0, $reminderMinutes - 15));
        $windowEnd = now()->addMinutes($reminderMinutes);
        $this->info("Ayar: dersten {$reminderMinutes} dakika önce. Pencere: {$windowStart->format('H:i')} - {$windowEnd->format('H:i')}");

        $turkishDays = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
        $todayName = $turkishDays[now()->dayOfWeekIso];
        $todayDate = today()->toDateString();

        $this->info("\n=== 4. Bugünkü ({$todayName}) Tüm Ders Programları ===");
        $schedules = LessonSchedule::with('group')->where('day_of_week', $todayName)->get();
        if ($schedules->isEmpty()) {
            $this->warn('Bugün için hiç ders programı kaydı yok.');
        }
        foreach ($schedules as $sch) {
            $start = Carbon::parse($todayDate . ' ' . $sch->start_time);
            $inWindow = $start->between($windowStart, $windowEnd);
            $dedupKey = "push-ders-{$sch->id}-{$todayDate}";
            $alreadySent = Cache::has($dedupKey);
            $this->line(sprintf(
                '- [%s] Grup: %s | Başlangıç: %s | Pencerede mi: %s | Daha önce gönderildi mi (dedup): %s',
                $sch->id,
                $sch->group?->name ?? '?',
                $start->format('H:i'),
                $inWindow ? 'EVET' : 'hayır',
                $alreadySent ? 'EVET' : 'hayır'
            ));
        }

        $this->info("\nSonuç: Eğer yukarıda ilgili ders 'Pencerede mi: EVET' VE 'Daha önce gönderildi: hayır' ise, bir sonraki 15 dakikalık zamanlayıcı tetiklemesinde (saat :00/:15/:30/:45) gönderilmelidir. Abonelik yoksa önce o adım tamamlanmalı.");

        return self::SUCCESS;
    }
}
