<?php

// المسار الكامل: database/migrations/2024_01_03_000001_create_customers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                           // اسم العميل
            $table->string('phone')->nullable();                              // رقم الهاتف
            $table->string('phone_alt')->nullable();                          // هاتف بديل
            $table->string('email')->nullable();                              // البريد الإلكتروني
            $table->text('address')->nullable();                              // العنوان
            $table->enum('type', ['individual', 'company'])                  // نوع العميل
                  ->default('individual');
            $table->string('company_name')->nullable();                       // اسم الشركة (إن وجد)
            $table->string('tax_number')->nullable();                         // الرقم الضريبي
            $table->enum('classification', ['vip', 'regular', 'inactive'])  // تصنيف العميل
                  ->default('regular');
            $table->decimal('credit_limit', 15, 2)->default(0);             // حد الائتمان
            $table->decimal('balance', 15, 2)->default(0);                  // الرصيد الحالي (مديونية)
            $table->text('notes')->nullable();                                // ملاحظات
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('phone');
            $table->index('classification');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
