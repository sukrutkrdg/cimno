<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * DatabaseSeeder sadece ilk kurulumda çalışır; canlıdaki mevcut veritabanlarına yeni
     * "bugün dersiniz var" hatırlatma şablonunu eklemek için veri migration'ı gerekir
     * (bkz. 2026_10_20_000004'teki aynı desen).
     */
    public function up(): void
    {
        $exists = DB::table('whatsapp_templates')->where('trigger_event', 'lesson_today')->exists();
        if ($exists) {
            return;
        }

        DB::table('whatsapp_templates')->insert([
            'shortcut' => '/bugun_ders',
            'title' => 'Bugün Dersiniz Var Hatırlatması',
            'trigger_event' => 'lesson_today',
            'category' => 'utility',
            'days_offset' => 0,
            'send_time' => '09:00:00',
            'message_template' => 'Günaydın {veli_adi} ☀️ Bugün {ogrenci_adi} için {grup_adi} dersimiz saat {saat} aralığında. Kendisini bu saatte salonumuzda bekliyoruz, görüşmek üzere! 🤸',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('whatsapp_templates')->where('trigger_event', 'lesson_today')->delete();
    }
};
