<?php

// Vercel Serverless PHP Giriş Noktası
// Yerel ortamda ve Natro cPanel'de public/index.php çalışır; Vercel'de ise bu dosya devreye girer.

$tmpDir = '/tmp/enerjik_storage';
$dirs = [
    $tmpDir,
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
if (!file_exists($sqliteTmp) && file_exists($sqliteSeed)) {
    @copy($sqliteSeed, $sqliteTmp);
    @chmod($sqliteTmp, 0666);
}

// Vercel ortam değişkenleri varsayılanları (Eğer Vercel panelinden özel MySQL tanımlanmadıysa otomatik hazır veritabanı kullanır)
$defaults = [
    'APP_NAME' => 'Enerjik Cimnastik',
    'APP_ENV' => 'production',
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
];

foreach ($defaults as $k => $v) {
    if (!getenv($k)) {
        putenv("$k=$v");
        $_ENV[$k] = $v;
        $_SERVER[$k] = $v;
    }
}

require __DIR__ . '/../public/index.php';
