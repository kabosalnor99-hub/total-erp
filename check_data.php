<?php
$railway = new mysqli(
    'shortline.proxy.rlwy.net',
    'root',
    'KeLbGEDZnelRdFYOCuZRqyipubZLgrhI',
    'railway',
    41632
);

if ($railway->connect_error) {
    die('Connection failed: ' . $railway->connect_error);
}

$result = $railway->query("SELECT COUNT(*) as count FROM categories");
$row = $result->fetch_assoc();
echo "Categories: {$row['count']} rows\n";

$result = $railway->query("SELECT COUNT(*) as count FROM products");
$row = $result->fetch_assoc();
echo "Products: {$row['count']} rows\n";

// عرض بعض الفئات
echo "\nFirst 5 categories:\n";
$result = $railway->query("SELECT id, name_ar, name_en FROM categories LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo "  {$row['id']}: {$row['name_ar']} ({$row['name_en']})\n";
}

// عرض بعض المنتجات
echo "\nFirst 5 products:\n";
$result = $railway->query("SELECT id, sku, name_ar, name_en FROM products LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo "  {$row['id']}: {$row['sku']} - {$row['name_ar']} ({$row['name_en']})\n";
}

$railway->close();
