<?php

// المسار الكامل: database/migrations/2024_01_05_000001_create_accounts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();                          // كود الحساب (1001, 1002...)
            $table->string('name_ar');                                 // اسم الحساب بالعربية
            $table->string('name_en')->nullable();                     // اسم الحساب بالإنجليزية
            $table->enum('type', [
                'asset',       // أصول
                'liability',   // خصوم
                'equity',      // حقوق ملكية
                'revenue',     // إيرادات
                'expense',     // مصروفات
            ]);
            $table->enum('normal_balance', ['debit', 'credit']);       // طبيعة الحساب
            $table->foreignId('parent_id')                             // الحساب الأب (شجرة)
                  ->nullable()
                  ->constrained('accounts')
                  ->nullOnDelete();
            $table->integer('level')->default(1);                      // مستوى الحساب في الشجرة
            $table->boolean('is_leaf')->default(true);                 // هل هو حساب تفصيلي (قابل للترحيل)
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);    // رصيد افتتاحي
            $table->enum('opening_balance_type', ['debit', 'credit'])->default('debit');
            $table->timestamps();

            $table->index('code');
            $table->index('type');
            $table->index('parent_id');
            $table->index('is_leaf');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
