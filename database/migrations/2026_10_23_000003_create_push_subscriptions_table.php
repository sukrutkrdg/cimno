<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Web Push abonelikleri telefon numarasına bağlanır (öğrenciye değil) — bir
     * veli portalı oturumu birden fazla öğrenciyi (kardeşleri) kapsayabilir ve
     * tarayıcı/cihaz aboneliği, o anki oturumdaki TÜM çocuklar için geçerli olmalı.
     */
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('parent_phone', 30);
            $table->string('endpoint', 500)->unique();
            $table->string('public_key');
            $table->string('auth_token');
            $table->string('content_encoding')->default('aesgcm');
            $table->timestamps();

            $table->index('parent_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
