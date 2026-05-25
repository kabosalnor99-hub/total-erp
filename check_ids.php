<?php
$local = new mysqli('127.0.0.1', 'root', '', 'tutalcompany_db');
if ($local->connect_error) {
    die('Connection failed: ' . $local->connect_error);
}

// عرض أول 5 منتجات
echo "First 5 local products:\n";
$result = $local->query("SELECT id, product_code, name FROM products ORDER BY id LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo "  {$row['id']}: {$row['product_code']} - {$row['name']}\n";
}

// عرض أكبر ID في المحلي
$result = $local->query("SELECT MAX(id) as max_id FROM products");
$row = $result->fetch_assoc();
echo "\nMax product ID in local: {$row['max_id']}\n";

$result = $local->query("SELECT MIN(id) as min_id FROM products");
$row = $result->fetch_assoc();
echo "Min product ID in local: {$row['min_id']}\n";

$local->close();
