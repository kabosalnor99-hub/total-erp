<?php

// المسار الكامل: database/migrations/2024_01_03_000002_create_invoices_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();                        // رقم الفاتورة (AUTO)
            $table->foreignId('customer_id')
                  ->nullable()
                  ->constrained('customers')
                  ->nullOnDelete();
            $table->foreignId('user_id')                                      // موظف المبيعات
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->enum('type', ['cash', 'credit', 'partial'])              // نوع الفاتورة
                  ->default('cash');
            $table->enum('status', [
                'draft',        // مسودة
                'confirmed',    // مؤكدة
                'paid',         // مدفوعة بالكامل
                'partial',      // مدفوعة جزئياً
                'cancelled',    // ملغاة
            ])->default('draft');
            $table->decimal('subtotal', 15, 2)->default(0);                  // الإجمالي قبل الخصم
            $table->decimal('discount_amount', 15, 2)->default(0);           // مبلغ الخصم
            $table->decimal('discount_percent', 8, 2)->default(0);           // نسبة الخصم %
            $table->decimal('tax_percent', 8, 2)->default(0);               // نسبة الضريبة %
            $table->decimal('tax_amount', 15, 2)->default(0);               // مبلغ الضريبة
            $table->decimal('total', 15, 2)->default(0);                    // الإجمالي النهائي
            $table->decimal('paid_amount', 15, 2)->default(0);              // المبلغ المدفوع
            $table->decimal('remaining_amount', 15, 2)->default(0);         // المبلغ المتبقي
            $table->date('due_date')->nullable();                             // تاريخ الاستحقاق
            $table->text('notes')->nullable();                                // ملاحظات
            $table->string('reference')->nullable();                          // مرجع خارجي
            $table->timestamps();
            $table->softDeletes();

            $table->index('invoice_number');
            $table->index('status');
            $table->index('type');
            $table->index('customer_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
