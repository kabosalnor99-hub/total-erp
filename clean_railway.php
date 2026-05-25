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

// حذف البيانات من جداول محددة
$tables = ['categories', 'products'];

foreach ($tables as $table) {
    echo "Deleting from $table...\n";
    if ($railway->query("DELETE FROM $table")) {
        echo "  ✅ Done\n";
    } else {
        echo "  ❌ Error: " . $railway->error . "\n";
    }
}

$railway->close();
