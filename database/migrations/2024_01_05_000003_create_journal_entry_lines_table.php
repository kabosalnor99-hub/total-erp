<?php

// المسار الكامل: database/migrations/2024_01_05_000003_create_journal_entry_lines_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id')                              // القيد
                  ->constrained('journal_entries')
                  ->cascadeOnDelete();
            $table->foreignId('account_id')                            // الحساب
                  ->constrained('accounts')
                  ->restrictOnDelete();
            $table->decimal('debit', 15, 2)->default(0);               // مدين
            $table->decimal('credit', 15, 2)->default(0);              // دائن
            $table->string('description')->nullable();                  // بيان السطر
            $table->integer('sort_order')->default(0);                  // ترتيب العرض
            $table->timestamps();

            $table->index('entry_id');
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
    }
};
