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
        // Gruba birden fazla antrenör atanabilmesi için (yardımcı antrenörler):
        // Group.coach_id baş antrenörü belirtmeye devam eder (mevcut tüm yetki/filtre
        // mantığı bunu kullanıyor, değiştirilmedi); bu ara tablo SADECE ek/yardımcı
        // antrenörleri tutar, "+ Antrenör Ekle" ile parametrik olarak çoğaltılabilir.
        Schema::create('group_coach', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['group_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_coach');
    }
};
