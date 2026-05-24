<?php
putenv('APP_ENV=local');
define('LARAVEL_START', microtime(true));

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

\Illuminate\Support\Facades\URL::forceScheme('http');
\Illuminate\Support\Facades\URL::forceRootUrl('http://127.0.0.1:8000');

$product = \App\Models\Product::whereNotNull('image')->first();

echo "DB Image: " . $product->image . "\n";
echo "Generated URL: " . $product->image_url . "\n";
echo "Asset function returns: " . asset($product->image) . "\n";
echo "Asset with direct path: " . asset('/images/products/test.jpg') . "\n";
