<?php

use Illuminate\Database\Migrations\Migration;
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
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `whatsapp_templates` MODIFY `trigger_event` VARCHAR(60) NOT NULL DEFAULT 'custom'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE whatsapp_templates ALTER COLUMN trigger_event TYPE VARCHAR(60)');
            DB::statement("ALTER TABLE whatsapp_templates ALTER COLUMN trigger_event SET DEFAULT 'custom'");
        }
        // SQLite: bkz. 2026_10_20_000001 migration'ındaki not (üretim hedefi Natro/MySQL'dir).
    }

    public function down(): void
    {
        // Serbest metin değerleri girildikten sonra sabit ENUM'a güvenle geri dönülemez.
    }
};
