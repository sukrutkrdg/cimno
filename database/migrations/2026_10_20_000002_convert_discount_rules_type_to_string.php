<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
     * Üretim (Vercel) SQLite kullandığı için o tarafı da tablo yeniden oluşturarak düzeltir
     * (bkz. 2026_10_20_000001 migration'ındaki aynı teknik).
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `discount_rules` MODIFY `type` VARCHAR(40) NOT NULL DEFAULT 'custom'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE discount_rules ALTER COLUMN type TYPE VARCHAR(40)');
            DB::statement("ALTER TABLE discount_rules ALTER COLUMN type SET DEFAULT 'custom'");
            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            Schema::create('discount_rules_rebuild', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type', 40)->default('custom');
                $table->decimal('percentage', 5, 2)->default(0);
                $table->decimal('fixed_amount', 10, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            DB::statement('INSERT INTO discount_rules_rebuild (id, name, type, percentage, fixed_amount, is_active, created_at, updated_at)
                SELECT id, name, type, percentage, fixed_amount, is_active, created_at, updated_at FROM discount_rules');

            Schema::drop('discount_rules');
            Schema::rename('discount_rules_rebuild', 'discount_rules');
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    public function down(): void
    {
        // Serbest metin değerleri girildikten sonra sabit ENUM'a güvenle geri dönülemez.
    }
};
