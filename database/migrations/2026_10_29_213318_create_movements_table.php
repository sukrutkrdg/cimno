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
        // Yönetim panelinden eklenip-çıkarılabilen cimnastik hareket kataloğu (ör. "Öne
        // Takla", "Amut", "Spagat"). Komple Takvim modülünde "hangi grup hangi gün hangi
        // hareketi çalışacak" eşlemesinde (schedule_movement ara tablosu, ayrı migration)
        // kullanılır.
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
