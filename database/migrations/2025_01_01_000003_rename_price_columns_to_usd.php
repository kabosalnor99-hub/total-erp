<?php

// المسار: database/migrations/2025_01_01_000003_rename_price_columns_to_usd.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // فقط نغير الاسم — البيانات تبقى كما هي
            $table->renameColumn('purchase_price', 'purchase_price_usd');
            $table->renameColumn('sale_price',     'price_usd');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // للرجوع للخلف عند الحاجة
            $table->renameColumn('purchase_price_usd', 'purchase_price');
            $table->renameColumn('price_usd',          'sale_price');
        });
    }
};
