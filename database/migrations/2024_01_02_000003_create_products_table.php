<?php

// المسار الكامل: database/migrations/2024_01_02_000003_create_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();                                  // كود المنتج
            $table->string('barcode')->nullable()->unique();                  // الباركود
            $table->string('name_ar');                                        // الاسم بالعربية
            $table->string('name_en')->nullable();                            // الاسم بالإنجليزية
            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('categories')
                  ->nullOnDelete();
            $table->string('brand')->nullable();                              // العلامة التجارية
            $table->string('unit')->default('قطعة');                         // وحدة القياس
            $table->decimal('purchase_price', 15, 2)->default(0);            // سعر الشراء
            $table->decimal('sale_price', 15, 2)->default(0);                // سعر البيع
            $table->decimal('profit_margin', 8, 2)->default(0);              // هامش الربح %
            $table->integer('quantity')->default(0);                          // الكمية الإجمالية
            $table->integer('reorder_point')->default(5);                     // حد الطلب الأدنى
            $table->string('image')->nullable();                              // الصورة الرئيسية
            $table->json('images')->nullable();                               // صور متعددة
            $table->enum('type', [
                'power_tools',    // أدوات كهربائية
                'hand_tools',     // أدوات يدوية
                'equipment',      // معدات
                'spare_parts',    // قطع غيار
                'other',          // أخرى
            ])->default('other');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // فهارس للبحث السريع
            $table->index('sku');
            $table->index('barcode');
            $table->index('category_id');
            $table->index('quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
