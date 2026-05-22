<?php

// المسار الكامل: database/migrations/2024_01_04_000002_create_pos_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();             // رقم الإيصال (AUTO)
            $table->foreignId('session_id')                        // الجلسة
                  ->constrained('pos_sessions')
                  ->cascadeOnDelete();
            $table->foreignId('customer_id')                       // العميل (اختياري)
                  ->nullable()
                  ->constrained('customers')
                  ->nullOnDelete();
            $table->foreignId('user_id')                           // الكاشير
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('invoice_id')                        // الفاتورة المرتبطة (م3)
                  ->nullable()
                  ->constrained('invoices')
                  ->nullOnDelete();

            // المبالغ
            $table->decimal('subtotal', 15, 2)->default(0);        // الإجمالي قبل الخصم
            $table->decimal('discount_amount', 15, 2)->default(0); // مبلغ الخصم
            $table->decimal('discount_percent', 8, 2)->default(0); // نسبة الخصم %
            $table->decimal('tax_percent', 8, 2)->default(0);      // نسبة الضريبة
            $table->decimal('tax_amount', 15, 2)->default(0);      // مبلغ الضريبة
            $table->decimal('total', 15, 2)->default(0);           // الإجمالي النهائي

            // الدفع
            $table->enum('payment_type', ['cash', 'credit', 'split'])->default('cash');
            $table->decimal('cash_amount', 15, 2)->default(0);     // المبلغ النقدي
            $table->decimal('credit_amount', 15, 2)->default(0);   // المبلغ الآجل
            $table->decimal('cash_received', 15, 2)->default(0);   // النقد المستلم فعلياً
            $table->decimal('change_amount', 15, 2)->default(0);   // الباقي المُرجَع

            $table->enum('status', ['completed', 'cancelled', 'held'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('receipt_number');
            $table->index('session_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_transactions');
    }
};
