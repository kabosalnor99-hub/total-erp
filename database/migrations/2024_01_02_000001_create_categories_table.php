<?php

// المسار الكامل: database/migrations/2024_01_02_000001_create_categories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');                                        // الاسم بالعربية
            $table->string('name_en')->nullable();                            // الاسم بالإنجليزية
            $table->foreignId('parent_id')                                    // فئة أب (للفئات الفرعية)
                  ->nullable()
                  ->constrained('categories')
                  ->nullOnDelete();
            $table->string('icon')->nullable();                               // أيقونة الفئة
            $table->string('color', 7)->nullable();                           // لون مميز
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
