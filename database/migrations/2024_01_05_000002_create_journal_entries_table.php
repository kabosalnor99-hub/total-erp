<?php

// المسار الكامل: database/migrations/2024_01_05_000002_create_journal_entries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number')->unique();                   // رقم القيد (AUTO: JE-2024-0001)
            $table->date('date');                                        // تاريخ القيد
            $table->string('description');                               // وصف القيد
            $table->foreignId('user_id')                                 // المحاسب
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->enum('status', ['draft', 'posted'])                 // مسودة / مُرحَّل
                  ->default('draft');
            $table->string('reference_type')->nullable();               // نوع المرجع (Invoice, PosTransaction...)
            $table->unsignedBigInteger('reference_id')->nullable();     // رقم المرجع
            $table->enum('source', [
                'manual',       // يدوي
                'invoice',      // من فاتورة بيع
                'payment',      // من دفعة
                'purchase',     // من مشتريات
                'payroll',      // من رواتب
                'pos',          // من نقطة البيع
                'adjustment',   // تسوية
            ])->default('manual');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('entry_number');
            $table->index('date');
            $table->index('status');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
