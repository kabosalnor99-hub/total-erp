<?php
$local = new mysqli('127.0.0.1', 'root', '', 'tutalcompany_db');
if ($local->connect_error) {
    die('Connection failed: ' . $local->connect_error);
}

$result = $local->query("SELECT COUNT(*) as count FROM categories");
$row = $result->fetch_assoc();
echo "Local categories: {$row['count']} rows\n";

$result = $local->query("SELECT COUNT(*) as count FROM products");
$row = $result->fetch_assoc();
echo "Local products: {$row['count']} rows\n";

$local->close();
