<?php

// المسار الكامل: database/seeders/ImportLocalDatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportLocalDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // قراءة ملف SQL المُصدّر
        $sqlFile = database_path('seeders/total_erp_local.sql');
        
        if (!File::exists($sqlFile)) {
            $this->command->warn('SQL file not found: ' . $sqlFile);
            return;
        }

        // قراءة محتوى ملف SQL
        $sql = File::get($sqlFile);
        
        // تقسيم SQL إلى جمل منفصلة
        $statements = $this->splitSqlStatements($sql);
        
        // تنفيذ كل جملة SQL
        $this->command->info('Importing local database...');
        
        foreach ($statements as $index => $statement) {
            if (empty(trim($statement))) {
                continue;
            }
            
            try {
                DB::statement($statement);
                
                if (($index + 1) % 100 === 0) {
                    $this->command->info('Imported ' . ($index + 1) . ' statements...');
                }
            } catch (\Exception $e) {
                $this->command->error('Error executing statement: ' . $e->getMessage());
                $this->command->error('Statement: ' . substr($statement, 0, 100) . '...');
            }
        }
        
        $this->command->info('Local database import completed!');
    }

    /**
     * تقسيم SQL إلى جمل منفصلة
     */
    private function splitSqlStatements(string $sql): array
    {
        // إزالة التعليقات
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // تقسيم بناءً على الفاصلة المنقوطة
        $statements = explode(';', $sql);
        
        // تنظيف الجمل
        $statements = array_map(function ($statement) {
            return trim($statement);
        }, $statements);
        
        // إزالة الجمل الفارغة
        $statements = array_filter($statements, function ($statement) {
            return !empty($statement);
        });
        
        return array_values($statements);
    }
}
