<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * leads.source sabit bir ENUM olarak tanımlanmıştı, ama Yönetim Paneli'ndeki
     * "Dinamik Seçenekler" (custom_lead_sources) özelliği bu alanı admin tarafından
     * serbestçe düzenlenebilir bir listeye çevirdi (örn. whatsapp, telefon, tavsiye).
     * ENUM/CHECK kısıtı bu değerleri kabul etmediği için MySQL'de "Data truncated"
     * hatası, SQLite'ta ise CHECK ihlali hatası veriyordu. Diğer dinamik alanlarla
     * (groups.area, groups.level, groups.age_group, financial_transactions.category)
     * aynı desene uyarak burayı da düz bir string'e çeviriyoruz.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `leads` MODIFY `source` VARCHAR(60) NOT NULL DEFAULT 'website'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE leads ALTER COLUMN source TYPE VARCHAR(60)');
            DB::statement("ALTER TABLE leads ALTER COLUMN source SET DEFAULT 'website'");
        }
        // SQLite'ta enum() bir CHECK kısıtına dönüşür ve bunu güvenle kaldırmak tablonun
        // yeniden oluşturulmasını (id/autoincrement kaybı riskiyle) gerektirir. Üretim hedefi
        // Natro/MySQL olduğu için SQLite tarafı kasıtlı olarak değiştirilmedi; yerel geliştirmede
        // SQLite kullanılıyorsa "dinamik kaynak" listesine sadece orijinal 5 enum değeriyle
        // (website, instagram, referral, walk_in, phone) uyumlu girişler eklenmelidir.
    }

    public function down(): void
    {
        // Serbest metin değerleri (whatsapp, telefon, tavsiye vb.) girildikten sonra
        // sabit ENUM'a güvenli şekilde geri dönülemez; kasıtlı olarak no-op.
    }
};
