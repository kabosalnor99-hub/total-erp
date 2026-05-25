<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class ImportProductsFromSql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-products-from-sql {--file=products_import_ar.sql}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from SQL file to Railway database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->option('file');
        $filePath = base_path($file);

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info("Reading SQL file: {$filePath}");
        $sql = file_get_contents($filePath);

        // Configure Railway database connection
        Config::set('database.connections.railway', [
            'driver' => 'mysql',
            'host' => 'shortline.proxy.rlwy.net',
            'port' => '41632',
            'database' => 'railway',
            'username' => 'root',
            'password' => 'KeLbGEDZnelRdFYOCuZRqyipubZLgrhI',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ]);

        try {
            $this->info("Connecting to Railway database...");
            $pdo = DB::connection('railway')->getPdo();
            
            // Remove comments
            $sql = preg_replace('/--.*$/m', '', $sql);
            
            // Replace INSERT with REPLACE INTO to update existing data
            $sql = str_replace('INSERT INTO', 'REPLACE INTO', $sql);
            
            $this->info("Executing SQL import using PDO (with REPLACE INTO to update existing data)...");
            
            $pdo->beginTransaction();
            
            // Use PDO exec to execute the entire SQL file
            $result = $pdo->exec($sql);
            
            $pdo->commit();
            
            $this->info("Successfully executed SQL on Railway database!");
            $this->info("Affected rows: {$result}");
            
            // Check existing data
            $categoryCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
            $productCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
            
            $this->info("Current database state:");
            $this->info("- Categories: {$categoryCount}");
            $this->info("- Products: {$productCount}");
            
            // Show sample categories to check Arabic names
            $this->info("\nSample categories from database:");
            $categories = $pdo->query("SELECT id, name_ar, name_en FROM categories LIMIT 5")->fetchAll();
            foreach ($categories as $cat) {
                $this->info("ID: {$cat['id']}, AR: {$cat['name_ar']}, EN: {$cat['name_en']}");
            }
            
            // Check product image paths
            $this->info("\nSample product image paths from database:");
            $products = $pdo->query("SELECT sku, name_ar, image FROM products LIMIT 5")->fetchAll();
            foreach ($products as $prod) {
                $this->info("SKU: {$prod['sku']}, Name: {$prod['name_ar']}, Image: {$prod['image']}");
            }
            
            // Update image paths to use correct path: /images/products/
            $this->info("\nUpdating image paths to use correct path /images/products/...");
            $updateResult = $pdo->exec("UPDATE products SET image = CONCAT('/images/', image) WHERE image IS NOT NULL AND image != '' AND image NOT LIKE '/images/%'");
            $this->info("Updated {$updateResult} image paths");
            
            // Show updated paths
            $this->info("\nUpdated image paths:");
            $products = $pdo->query("SELECT sku, name_ar, image FROM products LIMIT 5")->fetchAll();
            foreach ($products as $prod) {
                $this->info("SKU: {$prod['sku']}, Name: {$prod['name_ar']}, Image: {$prod['image']}");
            }
            
            // Check if images exist in local folder
            $this->info("\nChecking if images exist in local folder...");
            $localImagesPath = base_path('public/images/products');
            $localImageCount = count(glob("{$localImagesPath}/*.jpg"));
            $this->info("Local images count: {$localImageCount}");
            
            // Check for missing images
            $missingImages = $pdo->query("SELECT COUNT(*) FROM products WHERE image IS NOT NULL AND image != ''")->fetchColumn();
            $this->info("Products with images in database: {$missingImages}");
            
            if ($localImageCount >= $missingImages - 10) {
                $this->info("✓ Most images are available in local folder");
            } else {
                $this->warn("⚠ Some images may be missing from local folder");
            }
            
            // Check if images are accessible on Railway
            $this->info("\nChecking if images are accessible on Railway...");
            $railwayUrl = env('RAILWAY_PUBLIC_URL', 'http://localhost:8080');
            $testImageUrl = $railwayUrl . '/images/products/TDLI205581.jpg';
            
            $this->info("Testing image URL: {$testImageUrl}");
            
            try {
                $headers = @get_headers($testImageUrl);
                if ($headers && strpos($headers[0], '200') !== false) {
                    $this->info("✓ Images are accessible on Railway");
                } else {
                    $this->warn("⚠ Images may not be accessible on Railway yet");
                    $this->info("Note: Images will be available after next Railway deployment");
                }
            } catch (\Exception $e) {
                $this->warn("⚠ Could not check Railway image accessibility");
                $this->info("Note: Images will be copied to Railway during next deployment");
            }
            
            return 0;

        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $this->error("Error importing data: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }

    private function splitByInsert($sql)
    {
        // Split by semicolon, handling multi-line INSERT statements
        $statements = [];
        $lines = explode("\n", $sql);
        $currentStatement = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines and comments
            if (empty($line) || strpos($line, '--') === 0) {
                continue;
            }
            
            $currentStatement .= $line . "\n";
            
            // If line ends with semicolon, it's the end of a statement
            if (substr($line, -1) === ';') {
                $statements[] = trim($currentStatement);
                $currentStatement = '';
            }
        }
        
        return $statements;
    }

    private function splitSql($sql)
    {
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        
        // Split by semicolon, but handle quoted strings properly
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            
            if ($inString) {
                $current .= $char;
                if ($char === $stringChar && $sql[$i-1] !== '\\') {
                    $inString = false;
                }
                continue;
            }
            
            if ($char === '"' || $char === "'") {
                $current .= $char;
                $inString = true;
                $stringChar = $char;
                continue;
            }
            
            if ($char === ';') {
                $current .= $char;
                $statements[] = trim($current);
                $current = '';
                continue;
            }
            
            $current .= $char;
        }
        
        if (!empty(trim($current))) {
            $statements[] = trim($current);
        }
        
        return $statements;
    }
}
