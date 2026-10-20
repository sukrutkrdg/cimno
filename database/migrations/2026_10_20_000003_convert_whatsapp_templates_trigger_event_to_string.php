<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * whatsapp_templates.trigger_event ENUM'du (trial_reminder, class_cancelled, ... birthday, campaign).
     * Panelin "Yeni Hazır Cevap Şablonu" formu ise gizli bir "custom" değeri gönderiyor
     * (ayrıca alan adı "trigger_type" olarak yanlış yazılmıştı, bkz. AdminPanelController),
     * "custom" enum listesinde olmadığı için MySQL'de "Data truncated" hatası veriyordu.
     * Serbest metne çevriliyor; generateDailyWhatsappQueue() gibi otomatik tetikleyiciler
     * zaten where('trigger_event', '<deger>') ile aynı sabit string'leri aradığı için
     * davranış değişmez, sadece artık "custom" gibi serbest değerler de kabul edilir.
     * Üretim (Vercel) SQLite kullandığı için o tarafı da tablo yeniden oluşturarak düzeltir
     * (bkz. 2026_10_20_000001 migration'ındaki aynı teknik).
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `whatsapp_templates` MODIFY `trigger_event` VARCHAR(60) NOT NULL DEFAULT 'custom'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE whatsapp_templates ALTER COLUMN trigger_event TYPE VARCHAR(60)');
            DB::statement("ALTER TABLE whatsapp_templates ALTER COLUMN trigger_event SET DEFAULT 'custom'");
            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            Schema::create('whatsapp_templates_rebuild', function (Blueprint $table) {
                $table->id();
                $table->string('shortcut')->unique();
                $table->string('title');
                $table->string('trigger_event', 60)->default('custom');
                $table->enum('category', ['utility', 'marketing'])->default('utility');
                $table->integer('days_offset')->default(0);
                $table->time('send_time')->default('10:00:00');
                $table->text('message_template');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            DB::statement('INSERT INTO whatsapp_templates_rebuild (id, shortcut, title, trigger_event, category, days_offset, send_time, message_template, is_active, created_at, updated_at)
                SELECT id, shortcut, title, trigger_event, category, days_offset, send_time, message_template, is_active, created_at, updated_at FROM whatsapp_templates');

            Schema::drop('whatsapp_templates');
            Schema::rename('whatsapp_templates_rebuild', 'whatsapp_templates');
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    public function down(): void
    {
        // Serbest metin değerleri girildikten sonra sabit ENUM'a güvenle geri dönülemez.
    }
};
