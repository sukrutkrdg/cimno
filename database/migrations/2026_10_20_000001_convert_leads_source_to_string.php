<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
     *
     * NOT: Üretim (Vercel) şu an SQLite kullanıyor — "sadece MySQL'i düzelt" ilk
     * varsayımı yanlıştı. SQLite'ta ENUM bir CHECK kısıtına dönüştüğünden ve bunu
     * kaldırmanın tek güvenli yolu ALTER TABLE değil tablo yeniden oluşturmaktır;
     * id/autoincrement ve foreign key'ler korunarak bu aşağıda yapılır.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `leads` MODIFY `source` VARCHAR(60) NOT NULL DEFAULT 'website'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE leads ALTER COLUMN source TYPE VARCHAR(60)');
            DB::statement("ALTER TABLE leads ALTER COLUMN source SET DEFAULT 'website'");
            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            Schema::create('leads_rebuild', function (Blueprint $table) {
                $table->id();
                $table->string('parent_name');
                $table->string('parent_phone', 30);
                $table->string('student_name');
                $table->integer('student_age');
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('trial_group_id')->nullable()->constrained('groups')->nullOnDelete();
                $table->date('trial_date')->nullable();
                $table->string('preferred_days')->nullable();
                $table->string('source', 60)->default('website');
                $table->enum('status', ['new', 'trial_scheduled', 'thinking', 'converted', 'not_converted'])->default('new');
                $table->text('notes')->nullable();
                $table->boolean('kvkk_approved')->default(true);
                $table->timestamp('kvkk_approved_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            DB::statement('INSERT INTO leads_rebuild (id, parent_name, parent_phone, student_name, student_age, branch_id, trial_group_id, trial_date, preferred_days, source, status, notes, kvkk_approved, kvkk_approved_at, created_at, updated_at, deleted_at)
                SELECT id, parent_name, parent_phone, student_name, student_age, branch_id, trial_group_id, trial_date, preferred_days, source, status, notes, kvkk_approved, kvkk_approved_at, created_at, updated_at, deleted_at FROM leads');

            Schema::drop('leads');
            Schema::rename('leads_rebuild', 'leads');
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    public function down(): void
    {
        // Serbest metin değerleri (whatsapp, telefon, tavsiye vb.) girildikten sonra
        // sabit ENUM'a güvenli şekilde geri dönülemez; kasıtlı olarak no-op.
    }
};
