<?php

// المسار الكامل: database/migrations/2024_01_06_000001_create_suppliers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_number')->nullable();
            $table->enum('payment_terms', ['cash', 'net_7', 'net_15', 'net_30', 'net_60'])->default('cash');
            $table->unsignedTinyInteger('rating')->default(3)->comment('1-5 نجوم');
            $table->decimal('balance', 15, 2)->default(0)->comment('الرصيد المستحق للمورد');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
