<?php

// المسار الكامل: database/migrations/2024_01_02_000004_create_stock_movements_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();
            $table->foreignId('warehouse_id')
                  ->constrained('warehouses')
                  ->cascadeOnDelete();
            $table->enum('type', [
                'in',        // إضافة (شراء / إدخال يدوي)
                'out',       // إخراج (بيع / إخراج يدوي)
                'transfer',  // تحويل بين مستودعات
                'adjust',    // تسوية مخزون
                'return_in', // مرتجع شراء (يدخل للمخزون)
                'return_out',// مرتجع بيع (يخرج من المخزون)
            ]);
            $table->integer('quantity');                                       // الكمية (موجب دائماً)
            $table->integer('quantity_before')->default(0);                   // الكمية قبل الحركة
            $table->integer('quantity_after')->default(0);                    // الكمية بعد الحركة
            $table->decimal('unit_cost', 15, 2)->nullable();                  // تكلفة الوحدة
            $table->string('reference_type')->nullable();                      // App\Models\Invoice
            $table->unsignedBigInteger('reference_id')->nullable();           // رقم الفاتورة / أمر الشراء
            $table->foreignId('warehouse_to_id')                              // للتحويل بين مستودعات
                  ->nullable()
                  ->constrained('warehouses')
                  ->nullOnDelete();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->string('reason')->nullable();                             // سبب التسوية
            $table->text('notes')->nullable();
            $table->timestamps();

            // فهارس
            $table->index(['product_id', 'warehouse_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
