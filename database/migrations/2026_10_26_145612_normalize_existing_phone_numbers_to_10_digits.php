<?php

use App\Support\Phone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Telefon alanları artık her zaman başında 0 olmadan 10 hane olarak
     * saklanıyor (bkz. App\Support\Phone::normalize ve App\Rules\ValidPhoneNumber).
     * Bu, o standarttan önce kaydedilmiş mevcut satırları (o anki veriler demo/test
     * kaydı olduğu için büyük bir risk taşımıyor) aynı kalıba geçirir. `users.phone`
     * benzersiz (unique) olduğundan, normalize edilince aynı değere çakışan
     * satırlar (bozuk/tekrarlı demo verisi) olduğu gibi bırakılır, hata fırlatılmaz.
     */
    public function up(): void
    {
        $this->normalizeColumn('leads', 'parent_phone', allowNull: false, unique: false);
        $this->normalizeColumn('students', 'parent_phone', allowNull: false, unique: false);
        $this->normalizeColumn('students', 'secondary_parent_phone', allowNull: true, unique: false);
        $this->normalizeColumn('students', 'emergency_contact_phone', allowNull: true, unique: false);
        $this->normalizeColumn('whatsapp_messages', 'recipient_phone', allowNull: false, unique: false);
        $this->normalizeColumn('push_subscriptions', 'parent_phone', allowNull: false, unique: false);
        $this->normalizeColumn('users', 'phone', allowNull: true, unique: true);
    }

    private function normalizeColumn(string $table, string $column, bool $allowNull, bool $unique): void
    {
        $seen = [];
        if ($unique) {
            foreach (DB::table($table)->whereNotNull($column)->pluck($column) as $existing) {
                $seen[$existing] = true;
            }
        }

        DB::table($table)->whereNotNull($column)->orderBy('id')->select(['id', $column])
            ->each(function ($row) use ($table, $column, $allowNull, $unique, &$seen) {
                $original = $row->{$column};
                $normalized = Phone::normalize($original);

                if ($normalized === null) {
                    // Kurtarılamayan (boş, çok kısa, vs.) değer: zorunlu alanda olduğu
                    // gibi bırak, nullable alanda null'a çevir.
                    $normalized = $allowNull ? null : $original;
                }

                if ($normalized === $original) {
                    return;
                }

                if ($unique && $normalized !== null) {
                    if (isset($seen[$normalized])) {
                        return; // çakışma: bozuk demo verisi, dokunmadan bırak.
                    }
                    unset($seen[$original]);
                    $seen[$normalized] = true;
                }

                DB::table($table)->where('id', $row->id)->update([$column => $normalized]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Geri alınamaz: orijinal (0'lı/boşluklu) biçim saklanmıyor.
    }
};
