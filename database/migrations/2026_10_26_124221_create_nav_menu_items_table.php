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
     * Ana sayfa üst menüsü ("Branşlarımız", "Ders Programı" vb.) eskiden sabit kodlanmıştı
     * (bkz. home.blade.php'deki eski <nav> bloğu); artık yönetici panelden eklenebilir,
     * silinebilir, sıralanabilir ve hedefi değiştirilebilir hâle getiriliyor. Canlıdaki
     * mevcut siteyi menüsüz bırakmamak için eski 6 sabit öğe burada veri migration'ı
     * olarak (bkz. 2026_10_22_000001'deki aynı desen) tohumlanıyor.
     */
    public function up(): void
    {
        Schema::create('nav_menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            // 'anchor': aynı sayfada bir bölüme kaydırır (target = 'branslar' -> href="#branslar")
            // 'url': herhangi bir iç rotaya veya harici adrese gider (target = tam URL)
            $table->enum('link_type', ['anchor', 'url'])->default('anchor');
            $table->string('target');
            $table->boolean('open_in_new_tab')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        if (DB::table('nav_menu_items')->exists()) {
            return;
        }

        $now = now();
        DB::table('nav_menu_items')->insert([
            ['label' => 'Branşlarımız', 'link_type' => 'anchor', 'target' => 'branslar', 'open_in_new_tab' => false, 'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Ders Programı', 'link_type' => 'anchor', 'target' => 'program', 'open_in_new_tab' => false, 'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Paket & Ücretler', 'link_type' => 'anchor', 'target' => 'ucretler', 'open_in_new_tab' => false, 'sort_order' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Antrenörler', 'link_type' => 'anchor', 'target' => 'antrenorler', 'open_in_new_tab' => false, 'sort_order' => 4, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Galeri', 'link_type' => 'anchor', 'target' => 'galeri', 'open_in_new_tab' => false, 'sort_order' => 5, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'SSS', 'link_type' => 'anchor', 'target' => 'sss', 'open_in_new_tab' => false, 'sort_order' => 6, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nav_menu_items');
    }
};
