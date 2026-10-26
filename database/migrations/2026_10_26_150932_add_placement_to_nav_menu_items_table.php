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
     * Footer'daki "Veli Girişi / Salon QR Yoklama / QR Afiş" linkleri sabit kodluydu;
     * artık header menüsüyle aynı yönetim ekranından, ayrı bir "footer" yerleşimiyle
     * yönetilebiliyor. Mevcut 3 footer linki buraya veri olarak tohumlanıyor.
     */
    public function up(): void
    {
        Schema::table('nav_menu_items', function (Blueprint $table) {
            $table->string('placement')->default('header')->after('target'); // header | footer
        });

        if (DB::table('nav_menu_items')->where('placement', 'footer')->exists()) {
            return;
        }

        $now = now();
        DB::table('nav_menu_items')->insert([
            ['label' => 'Veli Girişi', 'link_type' => 'url', 'target' => '/mobil', 'placement' => 'footer', 'open_in_new_tab' => false, 'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Salon QR Yoklama', 'link_type' => 'url', 'target' => '/qr-yoklama', 'placement' => 'footer', 'open_in_new_tab' => false, 'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'QR Afiş', 'link_type' => 'url', 'target' => '/qr-afis', 'placement' => 'footer', 'open_in_new_tab' => false, 'sort_order' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nav_menu_items', function (Blueprint $table) {
            $table->dropColumn('placement');
        });
    }
};
