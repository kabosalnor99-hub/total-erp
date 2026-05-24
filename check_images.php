<?php
putenv('APP_ENV=local');
define('LARAVEL_START', microtime(true));

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$db = \Illuminate\Support\Facades\DB::class;

$images = \Illuminate\Support\Facades\DB::table('products')
    ->whereNotNull('image')
    ->distinct()
    ->limit(10)
    ->pluck('image');

foreach ($images as $img) {
    echo $img . "\n";
}
