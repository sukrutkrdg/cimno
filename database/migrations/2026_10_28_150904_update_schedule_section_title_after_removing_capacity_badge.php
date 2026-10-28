<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Ana sayfadaki "Haftalık Ders Programı & Kontenjanlar" başlığı ve "🟢 Kontenjan
     * bilgisi..." rozeti, grup kartlarındaki kontenjan/kayıt durumu rozetleriyle birlikte
     * kaldırıldı (salon sahibinin isteğiyle: öğrenci sayıları artık sitede görünmeyecek).
     * Site İçeriği formu daha önce kaydedilmişse bu eski metin veritabanında sabit
     * kalmış olabilir; yalnızca HÂLÂ eski varsayılan metinse (admin'in kasıtlı olarak
     * başka bir şey yazmadığı durum) güncelliyoruz — özel bir metin asla ezilmez.
     */
    public function up(): void
    {
        DB::table('site_settings')
            ->where('key', 'section_schedule_title')
            ->where('value', 'Haftalık Ders Programı & Kontenjanlar')
            ->update(['value' => 'Haftalık Ders Programı']);

        DB::table('site_settings')->where('key', 'schedule_live_badge')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Geri alınamaz: hangi satırların bu migrasyon tarafından değiştirildiği
        // ayrıca izlenmiyor, körü körüne geri yazmak başka bir değişikliği ezebilir.
    }
};
