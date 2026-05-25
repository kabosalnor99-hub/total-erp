<?php

// المسار: database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // استيراد قاعدة البيانات المحلية أولاً
        $this->call([
            ImportLocalDatabaseSeeder::class,
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            ChartOfAccountsSeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
            SupplierSeeder::class,
            SettingsSeeder::class,
        ]);
    }
}
