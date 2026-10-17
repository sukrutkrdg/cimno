<?php

use App\Models\Invoice;
use App\Models\Student;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Natro cPanel Paylaşımlı Hosting Zamanlayıcı Görevleri (cron * * * * * php artisan schedule:run)
Artisan::command('enerjik:daily-tasks', function () {
    $this->info('Enerjik Cimnastik günlük otomatik bildirim ve aidat kontrolleri çalıştırılıyor...');

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

    $this->info('Günlük görevler başarıyla tamamlandı.');
})->purpose('Günlük aidat, doğum günü, sağlık raporu ve deneme dersi hatırlatmalarını WhatsApp Business kuyruğuna ekler');

Schedule::command('enerjik:daily-tasks')->dailyAt('09:00');
