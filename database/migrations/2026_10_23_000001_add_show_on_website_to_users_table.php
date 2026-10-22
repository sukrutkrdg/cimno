<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Antrenör vitrin kartının anasayfada görünüp görünmeyeceği, personelin panele giriş
     * yapabilmesinden (is_active) ayrı bir karardır — örn. yeni işe başlayan bir antrenör
     * panele erişebilsin ama duyuru yapılana kadar sitede görünmesin, ya da eski bir
     * antrenörün geçmiş kayıtları (yoklama/hakediş) silinmeden sadece vitrinden kaldırılsın.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('show_on_website')->default(true)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('show_on_website');
        });
    }
};
