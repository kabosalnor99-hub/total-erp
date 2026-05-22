<?php

// المسار الكامل: database/migrations/2024_01_04_000003_create_pos_transaction_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')
                  ->constrained('pos_transactions')
                  ->cascadeOnDelete();
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();
            $table->decimal('quantity', 10, 3)->default(1);        // الكمية
            $table->decimal('unit_price', 15, 2)->default(0);      // السعر الأصلي
            $table->decimal('price', 15, 2)->default(0);           // سعر البيع الفعلي
            $table->decimal('discount_percent', 8, 2)->default(0); // خصم على المنتج %
            $table->decimal('discount_amount', 15, 2)->default(0); // مبلغ الخصم على المنتج
            $table->decimal('total', 15, 2)->default(0);           // إجمالي السطر
            $table->timestamps();

            $table->index('transaction_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_transaction_items');
    }
};
