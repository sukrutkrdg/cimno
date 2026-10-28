<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * "Kayıt Tarihi": öğrencinin fiilen kaydolduğu gün — sistem kaydının oluşturulduğu
     * created_at'ten ayrı tutulur, çünkü personel bir kaydı birkaç gün gecikmeli
     * girebilir ve raporlama/istatistik için gerçek kayıt gününün bilinmesi gerekir.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->date('registration_date')->nullable()->after('status');
        });

        // Mevcut kayıtlar için en iyi tahmin: sistem kaydının oluşturulduğu gün.
        DB::table('students')->whereNull('registration_date')->update([
            'registration_date' => DB::raw('DATE(created_at)'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('registration_date');
        });
    }
};
