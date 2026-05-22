<?php

// المسار الكامل: database/migrations/2024_01_03_000004_create_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();                        // رقم سند القبض
            $table->foreignId('invoice_id')
                  ->nullable()
                  ->constrained('invoices')
                  ->nullOnDelete();
            $table->foreignId('customer_id')
                  ->nullable()
                  ->constrained('customers')
                  ->nullOnDelete();
            $table->foreignId('user_id')                                       // الموظف المستلم
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->decimal('amount', 15, 2);                                  // المبلغ المستلم
            $table->enum('method', [
                'cash',         // نقدي
                'bank',         // تحويل بنكي
                'cheque',       // شيك
                'other',        // أخرى
            ])->default('cash');
            $table->string('reference')->nullable();                            // رقم الشيك / التحويل
            $table->date('payment_date');                                       // تاريخ الدفع
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('customer_id');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
