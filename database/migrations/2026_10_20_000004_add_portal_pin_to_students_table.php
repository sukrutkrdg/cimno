<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Veli portalı (/mobil, /qr-yoklama) önceden SADECE telefon numarasıyla eşleşme
     * yapıyordu — şifre/OTP yoktu. Numarayı bilen/tahmin eden herkes bir çocuğun
     * tam finansal bakiyesini, yoklama geçmişini ve gelişim karnesini görebiliyordu.
     * Bu alan, kayıt sırasında aileye verilen 4 haneli PIN'i tutar; telefon + PIN
     * ikisi birlikte doğrulanmadan portal girişi yapılamaz.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('portal_pin', 4)->nullable()->after('parent_phone');
        });

        // Mevcut kayıtlı öğrencilere (demo veya gerçek) rastgele PIN ata, aksi hâlde
        // portal girişi hiçbiri için çalışmaz.
        DB::table('students')->whereNull('portal_pin')->orderBy('id')->get(['id', 'parent_phone'])
            ->groupBy('parent_phone')
            ->each(function ($group) {
                $pin = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                DB::table('students')->whereIn('id', $group->pluck('id'))->update(['portal_pin' => $pin]);
            });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('portal_pin');
        });
    }
};
