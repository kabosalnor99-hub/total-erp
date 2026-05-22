<?php

// المسار الكامل: database/migrations/2024_01_02_000002_create_warehouses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                           // اسم المستودع
            $table->string('code')->unique()->nullable();                     // كود المستودع
            $table->text('location')->nullable();                             // الموقع / العنوان
            $table->string('manager_name')->nullable();                       // اسم مسؤول المستودع
            $table->string('phone')->nullable();                              // هاتف المستودع
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);                    // المستودع الافتراضي
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
