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
        // Komple Takvim modülü: bir ders saatinde (schedule = hangi gün + hangi grup)
        // o gün hangi hareket(ler)in çalışılacağını tutan ara tablo. Bir ders birden
        // fazla hareket içerebilir, bir hareket birden fazla derste tekrar edebilir.
        Schema::create('schedule_movement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->foreignId('movement_id')->constrained('movements')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['schedule_id', 'movement_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_movement');
    }
};
