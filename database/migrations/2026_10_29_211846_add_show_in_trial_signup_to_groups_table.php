<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            // "Yarışma Grubu" gibi herkese açık deneme dersi kaydına kapalı tutulması
            // gereken gruplar için: kapatılırsa bu grup deneme/aday ekranındaki
            // "Deneme Grubu" seçim listesinden gizlenir (grup yönetimi ve öğrenci
            // kayıt ekranlarında normal şekilde görünmeye devam eder).
            $table->boolean('show_in_trial_signup')->default(true)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('show_in_trial_signup');
        });
    }
};
