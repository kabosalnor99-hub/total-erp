<?php

// المسار الكامل: database/migrations/2024_01_05_000004_create_vouchers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number')->unique();                 // رقم السند (AUTO: RV-2024-0001)
            $table->enum('type', ['receipt', 'payment']);               // قبض / صرف
            $table->date('date');                                        // تاريخ السند
            $table->foreignId('account_id')                             // الحساب المقابل (العميل/المورد/المصروف)
                  ->constrained('accounts')
                  ->restrictOnDelete();
            $table->foreignId('cash_account_id')                        // حساب الصندوق أو البنك
                  ->constrained('accounts')
                  ->restrictOnDelete();
            $table->decimal('amount', 15, 2);                           // المبلغ
            $table->string('description');                               // البيان
            $table->enum('payment_method', ['cash', 'bank', 'cheque'])
                  ->default('cash');
            $table->string('cheque_number')->nullable();                 // رقم الشيك
            $table->string('bank_reference')->nullable();                // مرجع البنك
            $table->string('reference_type')->nullable();               // مرجع (Invoice, SupplierPayment...)
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('journal_entry_id')                       // القيد المحاسبي المرتبط
                  ->nullable()
                  ->constrained('journal_entries')
                  ->nullOnDelete();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('voucher_number');
            $table->index('type');
            $table->index('date');
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
