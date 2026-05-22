<?php

// المسار الكامل: database/migrations/2024_01_03_000003_create_invoice_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')
                  ->constrained('invoices')
                  ->cascadeOnDelete();
            $table->foreignId('product_id')
                  ->nullable()
                  ->constrained('products')
                  ->nullOnDelete();
            $table->string('product_name');                                   // اسم المنتج وقت الفاتورة
            $table->string('product_sku')->nullable();                        // كود المنتج
            $table->string('unit')->default('قطعة');                         // وحدة القياس
            $table->decimal('quantity', 10, 3)->default(1);                   // الكمية — موحَّد مع POS وباقي الجداول
            $table->decimal('unit_price', 15, 2);                            // سعر الوحدة
            $table->decimal('discount_percent', 8, 2)->default(0);           // خصم على المنتج %
            $table->decimal('discount_amount', 15, 2)->default(0);           // مبلغ الخصم
            $table->decimal('total', 15, 2);                                  // إجمالي البند
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
