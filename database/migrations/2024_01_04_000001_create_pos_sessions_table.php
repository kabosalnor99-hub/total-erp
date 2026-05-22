<?php

// المسار الكامل: database/migrations/2024_01_04_000001_create_pos_sessions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')                          // الكاشير
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->decimal('opening_balance', 15, 2)->default(0); // رصيد افتتاح الصندوق
            $table->decimal('closing_balance', 15, 2)->nullable(); // رصيد الإغلاق الفعلي
            $table->decimal('expected_balance', 15, 2)->default(0);// الرصيد المتوقع (محسوب)
            $table->decimal('total_sales', 15, 2)->default(0);    // إجمالي المبيعات
            $table->decimal('total_cash', 15, 2)->default(0);     // إجمالي المبيعات النقدية
            $table->decimal('total_credit', 15, 2)->default(0);   // إجمالي المبيعات الآجلة
            $table->decimal('total_discount', 15, 2)->default(0); // إجمالي الخصومات
            $table->decimal('cash_in', 15, 2)->default(0);        // نقدي مضاف (Petty Cash In)
            $table->decimal('cash_out', 15, 2)->default(0);       // نقدي مسحوب (Petty Cash Out)
            $table->integer('transactions_count')->default(0);    // عدد المعاملات
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('closing_notes')->nullable();             // ملاحظات الإغلاق
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('user_id');
            $table->index('opened_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_sessions');
    }
};
