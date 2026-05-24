<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

use App\Models\Product;

foreach(Product::limit(5)->get() as $p) {
    echo "Product ID: {$p->id}\n";
    echo "  DB Image: {$p->image}\n";
    echo "  Generated URL: {$p->image_url}\n";
    echo "  File exists: " . (file_exists(public_path($p->image)) ? 'YES' : 'NO') . "\n";
    echo "---\n";
}
