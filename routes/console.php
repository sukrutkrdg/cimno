<?php

use App\Models\Invoice;
use App\Models\Lead;
use App\Models\PushSubscription;
use App\Models\Schedule as LessonSchedule;
use App\Models\Student;
use App\Models\User;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Carbon;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;

// Acil Durum: Personel Şifresi Sıfırlama (e-posta/SMTP kurulana kadar tek kurtarma yolu)
// Sunucuda SSH/cPanel Terminal üzerinden çalıştırılır: php artisan admin:sifre-sifirla eposta@ornek.com YeniSifre123
Artisan::command('admin:sifre-sifirla {email} {sifre}', function (string $email, string $sifre) {
    $user = User::where('email', strtolower(trim($email)))->first();
    if (!$user) {
        $this->error("'{$email}' adresiyle kayıtlı bir personel bulunamadı.");
        return self::FAILURE;
    }

    $user->update(['password' => Hash::make($sifre)]);
    $this->info("{$user->name} ({$user->roleLabel()}) için şifre başarıyla güncellendi.");
    return self::SUCCESS;
})->purpose('Yönetim paneli için bir personelin şifresini sunucu erişimiyle sıfırlar (acil kurtarma)');

// Natro cPanel Paylaşımlı Hosting Zamanlayıcı Görevleri (cron * * * * * php artisan schedule:run)
Artisan::command('enerjik:daily-tasks', function () {
    $this->info('Enerjik Cimnastik günlük otomatik bildirim ve aidat kontrolleri çalıştırılıyor...');

    $turkishDays = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];

    // 1. Doğum günü olan öğrencilere otomatik kutlama mesajı kuyruklama
    $tplBirthday = WhatsappTemplate::where('trigger_event', 'birthday')->where('is_active', true)->first();
    if ($tplBirthday) {
        $birthdayStudents = Student::where('status', 'active')
            ->whereMonth('birth_date', now()->month)
            ->whereDay('birth_date', now()->day)
            ->get();

        foreach ($birthdayStudents as $st) {
            $text = str_replace(
                ['{veli_adi}', '{ogrenci_adi}'],
                [$st->parent_name, $st->full_name],
                $tplBirthday->message_template
            );
            WhatsappMessage::firstOrCreate([
                'template_id' => $tplBirthday->id,
                'student_id' => $st->id,
                'recipient_phone' => $st->parent_phone,
                'status' => 'queued',
            ], [
                'recipient_name' => $st->parent_name,
                'message_text' => $text,
            ]);
        }
    }

    // 2. Vadesine 3 gün kalan aidatlara hatırlatma kuyruklama
    $tplApproaching = WhatsappTemplate::where('trigger_event', 'due_approaching')->where('is_active', true)->first();
    if ($tplApproaching) {
        $targetDate = today()->addDays(abs($tplApproaching->days_offset ?: 3));
        $invoices = Invoice::with('student')->whereIn('status', ['unpaid', 'partial'])->whereDate('due_date', $targetDate)->get();
        foreach ($invoices as $inv) {
            if (!$inv->student) continue;
            $text = str_replace(
                ['{veli_adi}', '{ogrenci_adi}', '{donem}', '{tutar}', '{vade}'],
                [$inv->student->parent_name, $inv->student->full_name, $inv->period_label, number_format((float)$inv->remaining_amount, 2, ',', '.'), $inv->due_date->format('d.m.Y')],
                $tplApproaching->message_template
            );
            // Aynı gün içinde cron birden fazla kez tetiklenirse (paylaşımlı hosting'de
            // yaygın) aynı hatırlatmanın tekrar kuyruğa eklenmesini önler.
            $alreadyQueuedToday = WhatsappMessage::where('template_id', $tplApproaching->id)
                ->where('student_id', $inv->student->id)
                ->whereDate('created_at', today())
                ->exists();
            if ($alreadyQueuedToday) {
                continue;
            }

            WhatsappMessage::create([
                'template_id' => $tplApproaching->id,
                'student_id' => $inv->student->id,
                'recipient_name' => $inv->student->parent_name,
                'recipient_phone' => $inv->student->parent_phone,
                'message_text' => $text,
                'status' => 'queued',
            ]);
        }
    }

    // 3. Bugün dersi olan öğrencilere ders saati hatırlatması (birincil ve varsa ikincil veliye ayrı ayrı)
    $tplLessonToday = WhatsappTemplate::where('trigger_event', 'lesson_today')->where('is_active', true)->first();
    if ($tplLessonToday) {
        $todayName = $turkishDays[now()->dayOfWeekIso] ?? null;

        if ($todayName) {
            $studentsToday = Student::with(['group.schedules'])
                ->where('status', 'active')
                ->whereHas('group.schedules', fn($q) => $q->where('day_of_week', $todayName))
                ->get();

            foreach ($studentsToday as $st) {
                $times = $st->group?->schedules
                    ->where('day_of_week', $todayName)
                    ->map(fn($s) => substr($s->start_time, 0, 5) . '-' . substr($s->end_time, 0, 5))
                    ->implode(', ');
                if (!$times) continue;

                $recipients = collect([['name' => $st->parent_name, 'phone' => $st->parent_phone]]);
                if (!empty($st->secondary_parent_phone)) {
                    $recipients->push(['name' => $st->secondary_parent_name ?: $st->parent_name, 'phone' => $st->secondary_parent_phone]);
                }

                foreach ($recipients as $r) {
                    if (empty($r['phone'])) continue;

                    // Aynı gün cron birden fazla kez tetiklenirse (paylaşımlı hosting'de yaygın)
                    // aynı veliye aynı hatırlatmanın tekrar kuyruğa eklenmesini önler.
                    $alreadyQueuedToday = WhatsappMessage::where('template_id', $tplLessonToday->id)
                        ->where('student_id', $st->id)
                        ->where('recipient_phone', $r['phone'])
                        ->whereDate('created_at', today())
                        ->exists();
                    if ($alreadyQueuedToday) continue;

                    $text = str_replace(
                        ['{veli_adi}', '{ogrenci_adi}', '{saat}', '{grup_adi}'],
                        [$r['name'], $st->full_name, $times, $st->group?->name],
                        $tplLessonToday->message_template
                    );

                    WhatsappMessage::create([
                        'template_id' => $tplLessonToday->id,
                        'student_id' => $st->id,
                        'recipient_name' => $r['name'],
                        'recipient_phone' => $r['phone'],
                        'message_text' => $text,
                        'status' => 'queued',
                    ]);
                }
            }
        }
    }

    // 4. Yarın deneme dersi planlanan adaylara 1 gün önceden hatırlatma
    $tplTrial = WhatsappTemplate::where('trigger_event', 'trial_reminder')->where('is_active', true)->first();
    if ($tplTrial) {
        $targetDate = today()->addDays(abs($tplTrial->days_offset ?: 1));
        $targetDayName = $turkishDays[$targetDate->dayOfWeekIso] ?? null;

        $trialLeads = Lead::with('trialGroup.schedules')
            ->where('status', 'trial_scheduled')
            ->whereNotNull('trial_group_id')
            ->whereDate('trial_date', $targetDate)
            ->get();

        foreach ($trialLeads as $lead) {
            $schedule = $targetDayName
                ? $lead->trialGroup?->schedules->firstWhere('day_of_week', $targetDayName)
                : null;
            $saat = $schedule ? substr($schedule->start_time, 0, 5) . '-' . substr($schedule->end_time, 0, 5) : '-';

            $alreadyQueuedToday = WhatsappMessage::where('template_id', $tplTrial->id)
                ->where('recipient_phone', $lead->parent_phone)
                ->whereDate('created_at', today())
                ->exists();
            if ($alreadyQueuedToday) continue;

            $text = str_replace(
                ['{veli_adi}', '{ogrenci_adi}', '{tarih}', '{saat}'],
                [$lead->parent_name, $lead->student_name, $targetDate->format('d.m.Y'), $saat],
                $tplTrial->message_template
            );

            WhatsappMessage::create([
                'template_id' => $tplTrial->id,
                'recipient_name' => $lead->parent_name . ' (Aday - ' . $lead->student_name . ')',
                'recipient_phone' => $lead->parent_phone,
                'message_text' => $text,
                'status' => 'queued',
            ]);
        }
    }

    // 5. Sağlık raporu süresi yaklaşan öğrencilere otomatik hatırlatma
    $tplMedical = WhatsappTemplate::where('trigger_event', 'medical_report_expiring')->where('is_active', true)->first();
    if ($tplMedical) {
        $targetDate = today()->addDays(abs($tplMedical->days_offset ?: 15));
        $expiringStudents = Student::where('status', 'active')
            ->whereDate('medical_report_expires_at', $targetDate)
            ->get();

        foreach ($expiringStudents as $st) {
            $alreadyQueuedToday = WhatsappMessage::where('template_id', $tplMedical->id)
                ->where('student_id', $st->id)
                ->whereDate('created_at', today())
                ->exists();
            if ($alreadyQueuedToday) continue;

            $text = str_replace(
                ['{veli_adi}', '{ogrenci_adi}', '{vade}'],
                [$st->parent_name, $st->full_name, $st->medical_report_expires_at->format('d.m.Y')],
                $tplMedical->message_template
            );

            WhatsappMessage::create([
                'template_id' => $tplMedical->id,
                'student_id' => $st->id,
                'recipient_name' => $st->parent_name,
                'recipient_phone' => $st->parent_phone,
                'message_text' => $text,
                'status' => 'queued',
            ]);
        }
    }

    $this->info('Günlük görevler başarıyla tamamlandı.');
})->purpose('Günlük aidat, doğum günü, sağlık raporu, deneme dersi ve bugünkü ders saati hatırlatmalarını WhatsApp Business kuyruğuna ekler');

Schedule::command('enerjik:daily-tasks')->dailyAt('09:00');

// Tek Seferlik Kurulum: Web Push için VAPID anahtar çifti üretir. Çıktıyı .env
// dosyasına VAPID_PUBLIC_KEY / VAPID_PRIVATE_KEY olarak elle ekleyin.
Artisan::command('enerjik:vapid-anahtar-uret', function () {
    $keys = VAPID::createVapidKeys();
    $this->info('Aşağıdaki iki satırı .env dosyanıza ekleyin ve sunucuyu yeniden başlatın:');
    $this->line('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
    $this->line('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
    return self::SUCCESS;
})->purpose('Web Push bildirimleri için bir VAPID anahtar çifti üretir (yalnızca bir kez, ilk kurulumda çalıştırılır)');

// Ders Saati Yaklaşıyor Bildirimi: her 15 dakikada bir, 20-35 dakika içinde
// başlayacak dersleri bulup o gruptaki aktif öğrencilerin (hem birincil hem
// ikincil) velisine, veli portalına ekledikleri kısayoldan Web Push bildirimi
// gönderir. WhatsApp'tan farklı olarak gerçek zamanlıdır (günde bir kez değil).
Artisan::command('enerjik:ders-bildirimi-gonder', function () {
    $publicKey = config('services.vapid.public_key');
    $privateKey = config('services.vapid.private_key');
    if (!$publicKey || !$privateKey) {
        $this->warn('VAPID anahtarları tanımlı değil (php artisan enerjik:vapid-anahtar-uret ile üretin), push bildirimi atlanıyor.');
        return self::SUCCESS;
    }

    $turkishDays = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
    $todayName = $turkishDays[now()->dayOfWeekIso] ?? null;
    if (!$todayName) {
        return self::SUCCESS;
    }

    $windowStart = now()->addMinutes(20);
    $windowEnd = now()->addMinutes(35);
    $todayDate = today()->toDateString();

    $upcoming = LessonSchedule::with('group')
        ->where('day_of_week', $todayName)
        ->get()
        ->filter(function ($sch) use ($todayDate, $windowStart, $windowEnd) {
            $start = Carbon::parse($todayDate . ' ' . $sch->start_time);
            return $start->between($windowStart, $windowEnd);
        });

    if ($upcoming->isEmpty()) {
        $this->info('20-35 dakika içinde başlayacak ders bulunamadı.');
        return self::SUCCESS;
    }

    $webPush = new WebPush(['VAPID' => [
        'subject' => config('services.vapid.subject'),
        'publicKey' => $publicKey,
        'privateKey' => $privateKey,
    ]]);
    $queued = 0;

    foreach ($upcoming as $sch) {
        // Aynı ders aralığı birden fazla cron tetiklemesine denk gelebilir (ör.
        // zamanlayıcı birkaç dakika gecikmeli çalışırsa); her ders+gün için tek
        // seferlik gönderim garantisi için kısa süreli bir önbellek kilidi kullanılır.
        $dedupKey = "push-ders-{$sch->id}-{$todayDate}";
        if (Cache::has($dedupKey)) {
            continue;
        }
        Cache::put($dedupKey, true, now()->addHours(20));

        $students = Student::where('group_id', $sch->group_id)->where('status', 'active')->get();
        if ($students->isEmpty()) {
            continue;
        }

        $phones = collect();
        foreach ($students as $st) {
            $phones->push(preg_replace('/\D+/', '', (string) $st->parent_phone));
            if (!empty($st->secondary_parent_phone)) {
                $phones->push(preg_replace('/\D+/', '', (string) $st->secondary_parent_phone));
            }
        }
        $phones = $phones->filter()->unique()->values();
        if ($phones->isEmpty()) {
            continue;
        }

        $subs = PushSubscription::whereIn('parent_phone', $phones)->get();
        $saat = substr($sch->start_time, 0, 5);
        $payload = json_encode([
            'title' => '🤸 Ders Saati Yaklaşıyor',
            'body' => ($sch->group?->name ?? 'Dersiniz') . " dersi saat {$saat}'de başlıyor!",
            'url' => '/mobil',
            'tag' => $dedupKey,
        ], JSON_UNESCAPED_UNICODE);

        foreach ($subs as $sub) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                    'contentEncoding' => $sub->content_encoding,
                ]),
                $payload
            );
            $queued++;
        }
    }

    if ($queued > 0) {
        foreach ($webPush->flush() as $report) {
            // Tarayıcı/kullanıcı aboneliği kalıcı olarak iptal ettiyse (ör. bildirim
            // izni geri alındı) artık geçersiz olan kaydı veritabanından temizle.
            if (!$report->isSuccess() && $report->isSubscriptionExpired()) {
                PushSubscription::where('endpoint', $report->getEndpoint())->delete();
            }
        }
    }

    $this->info("{$queued} push bildirimi gönderildi.");
    return self::SUCCESS;
})->purpose('20-35 dakika içinde başlayacak derslerin velilerine Web Push "ders saati yaklaşıyor" bildirimi gönderir');

Schedule::command('enerjik:ders-bildirimi-gonder')->everyFifteenMinutes();
