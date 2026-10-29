<?php

namespace App\Console\Commands;

use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DiagnoseWhatsappEmoji extends Command
{
    /**
     * Tek seferlik teşhis komutu: "deneme dersi whatsapp mesajında emoji çıkmıyor"
     * şikayetini araştırmak için whatsapp_templates tablosunun karakter setini ve
     * admin panelinden kayıtlı "trial_reminder" şablonunun gerçek (ham byte) içeriğini
     * raporlar. Uzun/iç içe tırnaklı tek satır cPanel cron komutları güvenilir
     * çalışmadığından (muhtemelen alan uzunluğu sınırı), bu küçük komut olarak yazıldı.
     */
    protected $signature = 'enerjik:emoji-teshis';

    protected $description = 'WhatsApp şablon tablosunun karakter setini ve trial_reminder şablonunun ham içeriğini raporlar';

    public function handle(): int
    {
        $col = DB::selectOne(
            'SELECT CHARACTER_SET_NAME, COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?',
            ['whatsapp_templates', 'message_template']
        );
        $this->info('Sablon kolon charset: ' . ($col->CHARACTER_SET_NAME ?? 'yok') . ' / ' . ($col->COLLATION_NAME ?? 'yok'));

        $templates = WhatsappTemplate::all();
        foreach ($templates as $tpl) {
            $this->info("--- [{$tpl->trigger_event}] {$tpl->title} ---");
            $this->line($tpl->message_template);
            $this->line('Hex: ' . bin2hex($tpl->message_template));
        }

        $last = WhatsappMessage::where('recipient_name', 'like', '%Aday%')->latest()->first();
        if ($last) {
            $this->info('--- Son aday (deneme dersi) mesajı ---');
            $this->line($last->message_text);
            $this->line('Hex: ' . bin2hex($last->message_text));
            $this->line('wa_me_url: ' . $last->wa_me_url);
        } else {
            $this->warn('Henüz "Aday" içeren kayıtlı bir WhatsApp mesajı yok.');
        }

        return self::SUCCESS;
    }
}
