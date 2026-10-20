<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * discount_rules.type ENUM(sibling, upfront, scholarship, custom) olarak tanımlıydı,
     * ancak Yönetim Paneli'ndeki "Yeni İndirim Ekle" formu 'term' ("Dönem") değerini
     * gönderiyor — bu değer enum listesinde yok ve MySQL'de "Data truncated" hatası verir
     * (SQLite'ta ise CHECK ihlali). type alanı sadece etiket/gösterim amaçlı kullanıldığından
     * (hesaplamalar percentage/fixed_amount üzerinden yapılıyor) serbest string'e çeviriyoruz.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `discount_rules` MODIFY `type` VARCHAR(40) NOT NULL DEFAULT 'custom'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE discount_rules ALTER COLUMN type TYPE VARCHAR(40)');
            DB::statement("ALTER TABLE discount_rules ALTER COLUMN type SET DEFAULT 'custom'");
        }
        // SQLite: bkz. 2026_10_20_000001 migration'ındaki not (üretim hedefi Natro/MySQL'dir).
    }

    public function down(): void
    {
        // Serbest metin değerleri girildikten sonra sabit ENUM'a güvenle geri dönülemez.
    }
};
