<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. إضافة أعمدة التحويل البنكي لجدول pos_transactions
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->string('bank_ref_number', 100)->nullable()->after('payment_type')
                  ->comment('رقم مرجع التحويل البنكي');
            $table->string('bank_name', 100)->nullable()->after('bank_ref_number')
                  ->comment('اسم البنك');
        });

        // 2. تغيير enum payment_type لإضافة bank_transfer
        DB::statement("ALTER TABLE pos_transactions MODIFY COLUMN payment_type ENUM('cash','credit','split','bank_transfer') NOT NULL DEFAULT 'cash'");

        // 3. تغيير enum method في جدول payments لإضافة bank_transfer (إن وُجد)
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'method')) {
            DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('cash','bank_transfer','cheque','other') NOT NULL DEFAULT 'cash'");
        }
    }

    public function down(): void
    {
        // إعادة enum لحالته الأصلية
        DB::statement("ALTER TABLE pos_transactions MODIFY COLUMN payment_type ENUM('cash','credit','split') NOT NULL DEFAULT 'cash'");

        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->dropColumn(['bank_ref_number', 'bank_name']);
        });

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'method')) {
            DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('cash','cheque','other') NOT NULL DEFAULT 'cash'");
        }
    }
};
