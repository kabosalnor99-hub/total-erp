<?php

// المسار: database/migrations/2025_01_01_000001_create_exchange_rates_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();

            // سعر الصرف: كم جنيه سوداني مقابل 1 دولار
            $table->decimal('rate', 12, 4)->comment('SDG per 1 USD');

            // تاريخ سريان السعر
            $table->date('effective_date');

            // ملاحظات
            $table->string('notes')->nullable();

            // هل هذا السعر هو الفعّال الآن؟
            $table->boolean('is_active')->default(false)->index();

            // السعر السابق قبل التغيير (للمقارنة)
            $table->decimal('previous_rate', 12, 4)->nullable();

            // نسبة التغيير
            $table->decimal('change_percent', 8, 2)->nullable();

            // المستخدم الذي أدخل السعر
            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();

            $table->index('effective_date');
            $table->index(['is_active', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
