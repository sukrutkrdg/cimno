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
        // Salonun basılı "GELİŞİM TAKİP KARNESİ" formuna göre: 3 kategori × 4 kriter,
        // her biri 1-4 ölçeğinde (1-Öğreniyor, 2-Ortalama, 3-Mükemmel, 4-Tamamlandı).
        // Eski 0-100 puanlı sütunlar (flexibility_score vb.) DOKUNULMADAN kalır — geçmiş
        // kayıtlar olduğu gibi arşivde görünmeye devam eder. Yeni kayıtlar bu sütunları
        // kullanır; bir kaydın "yeni formatta" olup olmadığı phys_flexibility'nin dolu
        // olup olmamasına bakılarak anlaşılır (bkz. DevelopmentRecord::isPdfFormat()).
        Schema::table('development_records', function (Blueprint $table) {
            // Fiziksel Performans Analizi
            $table->unsignedTinyInteger('phys_flexibility')->nullable()->after('discipline_score');
            $table->unsignedTinyInteger('phys_balance_coordination')->nullable()->after('phys_flexibility');
            $table->unsignedTinyInteger('phys_strength_endurance')->nullable()->after('phys_balance_coordination');
            $table->unsignedTinyInteger('phys_focus')->nullable()->after('phys_strength_endurance');
            // Teknik Beceri (Hedef)
            $table->unsignedTinyInteger('tech_forward_backward_roll')->nullable()->after('phys_focus');
            $table->unsignedTinyInteger('tech_handstand_wheel')->nullable()->after('tech_forward_backward_roll');
            $table->unsignedTinyInteger('tech_eagle_split')->nullable()->after('tech_handstand_wheel');
            $table->unsignedTinyInteger('tech_flexibility_cooldown')->nullable()->after('tech_eagle_split');
            // Sosyal Gelişim ve Disiplin
            $table->unsignedTinyInteger('social_class_participation')->nullable()->after('tech_flexibility_cooldown');
            $table->unsignedTinyInteger('social_communication')->nullable()->after('social_class_participation');
            $table->unsignedTinyInteger('social_command_following')->nullable()->after('social_communication');
            $table->unsignedTinyInteger('social_goal_progress')->nullable()->after('social_command_following');

            // Not: eski 0-100 puanlı sütunlar NOT NULL + varsayılanlı kaldı (doctrine/dbal
            // kurulu değil, nullable'a çevirmek için gerekirdi — bağımlılık eklemeden
            // kaçınıldı). Yeni formatta girilen kayıtlarda bu eski sütunlar varsayılan
            // değerleriyle (80/80/80/80/90) dolu kalır ama hiçbir ekranda gösterilmez;
            // bir kaydın "yeni formatta" olup olmadığı SADECE phys_flexibility'nin dolu
            // olup olmamasına bakılarak belirlenir (bkz. DevelopmentRecord::isPdfFormat()).
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('development_records', function (Blueprint $table) {
            $table->dropColumn([
                'phys_flexibility', 'phys_balance_coordination', 'phys_strength_endurance', 'phys_focus',
                'tech_forward_backward_roll', 'tech_handstand_wheel', 'tech_eagle_split', 'tech_flexibility_cooldown',
                'social_class_participation', 'social_communication', 'social_command_following', 'social_goal_progress',
            ]);
        });
    }
};
