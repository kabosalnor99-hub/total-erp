<?php

// المسار الكامل: database/seeders/ImportLocalDatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportLocalDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $sqlFile = database_path('seeders/total_erp_local.sql');

        if (!File::exists($sqlFile)) {
            $this->command->warn('⚠️  SQL file not found: ' . $sqlFile . ' — skipping import.');
            return;
        }

        $sql = File::get($sqlFile);
        $statements = $this->splitSqlStatements($sql);

        $this->command->info('Importing local database...');

        $errors = 0;
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
                $errors++;
                // سجّل الخطأ الأول فقط لتجنب فيضان اللوقات
                if ($errors <= 5) {
                    $this->command->warn('Statement error #' . $errors . ': ' . $e->getMessage());
                } elseif ($errors === 6) {
                    $this->command->warn('... (further statement errors suppressed)');
                }
            }
        }

        if ($errors > 0) {
            $this->command->warn("Import completed with {$errors} error(s).");
        } else {
            $this->command->info('✅ Local database import completed successfully!');
        }
    }

    private function splitSqlStatements(string $sql): array
    {
        // إزالة تعليقات MySQL الشرطية (/*!40101 ... */) قبل المعالجة
        // هذه التعليقات تُنفَّذ في MySQL لكن splitSqlStatements تكسرها
        $sql = preg_replace('/\/\*![\d]+.*?\*\//s', '', $sql);

        // إزالة التعليقات العادية
        $sql = preg_replace('/--[^\n]*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

        // تقسيم بناءً على الفاصلة المنقوطة
        $statements = explode(';', $sql);

        return array_values(array_filter(
            array_map('trim', $statements),
            fn($s) => $s !== ''
        ));
    }
}
