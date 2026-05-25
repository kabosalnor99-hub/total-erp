<?php

// محلي
$local = new mysqli('127.0.0.1', 'root', '', 'tutalcompany_db');
if ($local->connect_error) {
    die('Local connection failed: ' . $local->connect_error);
}

// Railway
$railway = new mysqli('shortline.proxy.rlwy.net', 'root', 'KeLbGEDZnelRdFYOCuZRqyipubZLgrhI', 'railway', 41632);
if ($railway->connect_error) {
    die('Railway connection failed: ' . $railway->connect_error);
}

$tables = ['categories', 'products'];

foreach ($tables as $table) {
    echo "\n===== $table =====\n";
    echo "LOCAL:\n";
    $result = $local->query("DESCRIBE $table");
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['Field']} - {$row['Type']} ({$row['Null']})\n";
    }
    
    echo "RAILWAY:\n";
    $result = $railway->query("DESCRIBE $table");
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['Field']} - {$row['Type']} ({$row['Null']})\n";
    }
}

$local->close();
$railway->close();
