<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsImportArSeeder extends Seeder
{
    public function run(): void
    {
        $sql = file_get_contents(base_path('products_import_ar.sql'));
        DB::unprepared($sql);
    }
}
