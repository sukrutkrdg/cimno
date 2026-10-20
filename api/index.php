<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

try {
    $tmpDir = '/tmp/enerjik_storage';
    $dirs = [
        $tmpDir,
        "$tmpDir/bootstrap_cache",
        "$tmpDir/framework",
        "$tmpDir/framework/views",
        "$tmpDir/framework/cache",
        "$tmpDir/framework/cache/data",
        "$tmpDir/framework/sessions",
        "$tmpDir/logs",
    ];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
    }

    $sqliteTmp = '/tmp/database.sqlite';
    $sqliteSeed = __DIR__ . '/../database/vercel.sqlite';
    $isFreshDatabase = false;

    if (!file_exists($sqliteTmp) || filesize($sqliteTmp) < 1024) {
        if (file_exists($sqliteSeed) && filesize($sqliteSeed) > 1024) {
            @copy($sqliteSeed, $sqliteTmp);
            @chmod($sqliteTmp, 0666);
        } else {
            @touch($sqliteTmp);
            @chmod($sqliteTmp, 0666);
            $isFreshDatabase = true;
        }
    }

    $defaults = [
        'VERCEL' => '1',
        'APP_NAME' => 'Enerjik Cimnastik',
        'APP_ENV' => 'local',
        'APP_DEBUG' => 'true',
        'APP_KEY' => 'base64:No2Co5Ohscd6yQ7DzcTWf85P4Q0eI4ouGCC98rat7ak=',
        'APP_LOCALE' => 'tr',
        'APP_FALLBACK_LOCALE' => 'tr',
        'DB_CONNECTION' => 'sqlite',
        'DB_DATABASE' => $sqliteTmp,
        'VIEW_COMPILED_PATH' => "$tmpDir/framework/views",
        'SESSION_DRIVER' => 'cookie',
        'CACHE_STORE' => 'array',
        'LOG_CHANNEL' => 'stderr',
        'QUEUE_CONNECTION' => 'sync',
        'APP_SERVICES_CACHE' => "$tmpDir/bootstrap_cache/services.php",
        'APP_PACKAGES_CACHE' => "$tmpDir/bootstrap_cache/packages.php",
        'APP_CONFIG_CACHE' => "$tmpDir/bootstrap_cache/config.php",
        'APP_ROUTES_CACHE' => "$tmpDir/bootstrap_cache/routes.php",
        'APP_EVENTS_CACHE' => "$tmpDir/bootstrap_cache/events.php",
    ];

    foreach ($defaults as $k => $v) {
        if (!getenv($k)) {
            putenv("$k=$v");
            $_ENV[$k] = $v;
            $_SERVER[$k] = $v;
        }
    }

    // URI düzeltmesi (Vercel serverless yönlendirmesi için)
    if (isset($_SERVER['REQUEST_URI'])) {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';
    }

    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->useStoragePath($tmpDir);

    // ÖNEMLİ: database/vercel.sqlite deploy'da committed/dondurulmuş bir "tohum"
    // dosyasıdır. Her yeni migration eklendiğinde bu dosyayı yeniden üretip commit
    // etmeyi unutmak kolaydır — önceden bu durumda /tmp'deki veritabanı sonsuza
    // kadar eski şemada kalıyordu (örn. "no column named portal_pin" 500 hatası).
    // Artık her soğuk başlangıçta bekleyen migration'lar varsa otomatik uygulanır;
    // zaten uygulanmış migration'lar için bu çağrı no-op'tur, bu yüzden her istekte
    // çalıştırmak güvenli ve ucuzdur. Gerçek kullanıcı verisini SİLMEZ (migrate:fresh
    // DEĞİL, sade migrate).
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    if ($isFreshDatabase) {
        $kernel->call('migrate:fresh', ['--seed' => true, '--force' => true]);
    } else {
        $kernel->call('migrate', ['--force' => true]);
    }

    $request = \Illuminate\Http\Request::capture();
    $app->handleRequest($request);
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<h2>Enerjik Cimnastik - Sunucu Hatası Detayı</h2>';
    echo '<pre>' . htmlspecialchars($e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine() . "\n\n" . $e->getTraceAsString()) . '</pre>';
}
