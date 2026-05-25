<?php
// الاتصال بـ MySQL المحلي
$local = new mysqli('127.0.0.1', 'root', '', 'tutalcompany_db');
if ($local->connect_error) {
    die('Local DB connection failed: ' . $local->connect_error);
}
$local->set_charset("utf8mb4");

// الاتصال بـ Railway
$railway = new mysqli(
    'shortline.proxy.rlwy.net',
    'root',
    'KeLbGEDZnelRdFYOCuZRqyipubZLgrhI',
    'railway',
    41632
);
if ($railway->connect_error) {
    die('Railway DB connection failed: ' . $railway->connect_error);
}
$railway->set_charset("utf8mb4");

// تعريف التحويلات بين الأعمدة
$columnMappings = [
    'categories' => [
        'id' => 'id',
        'name' => 'name_ar',
        'parent_id' => 'parent_id',
        'icon' => 'icon',
        'sort_order' => 'sort_order',
        'is_active' => 'is_active',
        'created_at' => 'created_at'
    ],
    'products' => [
        'id' => 'id',
        'product_code' => 'sku',
        'barcode' => 'barcode',
        'name' => 'name_ar',
        'category_id' => 'category_id',
        'brand' => 'brand',
        'unit' => 'unit',
        'cost_price' => 'purchase_price',
        'retail_price' => 'sale_price',
        'image' => 'image',
        'is_active' => 'is_active',
        'description' => 'description',
        'created_by' => 'created_by',
        'created_at' => 'created_at'
    ]
];

// الأعمدة التي يجب ملأها بقيم افتراضية
$defaultValues = [
    'categories' => ['name_en' => null],
    'products' => ['name_en' => null, 'profit_margin' => 0, 'quantity' => 0, 'reorder_point' => 0, 'type' => 'other']
];

// الجداول المراد نقل بياناتها
$tables = ['categories', 'products'];

foreach ($tables as $table) {
    echo "Migrating '$table'...\n";
    flush();
    
    $result = $local->query("SELECT * FROM $table");
    
    if (!$result) {
        echo "  ❌ Error reading from local: " . $local->error . "\n";
        continue;
    }
    
    $count = 0;
    $failed = 0;
    $rowNum = 0;
    
    while ($row = $result->fetch_assoc()) {
        $rowNum++;
        $insertCols = [];
        $insertVals = [];
        
        // أضف الأعمدة المعروضة
        if (isset($columnMappings[$table])) {
            foreach ($columnMappings[$table] as $localCol => $railwayCol) {
                if (isset($row[$localCol])) {
                    $insertCols[] = $railwayCol;
                    $val = $row[$localCol];
                    if ($val === '' || $val === null) {
                        $insertVals[] = 'NULL';
                    } else {
                        $insertVals[] = "'" . $railway->real_escape_string($val) . "'";
                    }
                }
            }
        }
        
        // أضف القيم الافتراضية
        if (isset($defaultValues[$table])) {
            foreach ($defaultValues[$table] as $col => $val) {
                // لا تضيفه إذا كان موجود بالفعل
                if (!in_array($col, $insertCols)) {
                    $insertCols[] = $col;
                    $insertVals[] = $val === null ? 'NULL' : "'" . $railway->real_escape_string($val) . "'";
                }
            }
        }
        
        if (empty($insertCols)) {
            continue;
        }
        
        $cols = implode('`, `', $insertCols);
        $vals = implode(", ", $insertVals);
        
        $sql = "INSERT INTO `$table` (`$cols`) VALUES ($vals)";
        
        if (!$railway->query($sql)) {
            $failed++;
            if ($failed <= 3) {
                echo "  Error on row $rowNum: " . $railway->error . "\n";
            }
        } else {
            $count++;
            // اطبع التقدم كل 100 صف
            if ($count % 100 === 0) {
                echo "  ✓ Processed $count rows...\n";
                flush();
            }
        }
    }
    
    echo "  ✅ Inserted $count rows" . ($failed > 0 ? " ($failed failed)" : "") . "\n";
    flush();
}

$local->close();
$railway->close();

echo "\n✅ Migration completed successfully!\n";

$local->close();
$railway->close();
?>
