<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Natro/paylaşımlı hosting gibi ortamlarda eski MySQL/MariaDB (utf8mb4 + innodb_large_prefix
        // kapalı) sürümleri 767 byte'tan uzun unique index'lerde hata verebilir. Varsayılan string
        // uzunluğunu 191'e sabitlemek, migration'lardaki unique() string kolonları için güvenli.
        Schema::defaultStringLength(191);
    }
}
