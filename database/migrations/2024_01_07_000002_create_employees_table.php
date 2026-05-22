<?php

// المسار الكامل: database/migrations/2024_01_07_000002_create_employees_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_number')->unique();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('national_id')->nullable()->unique();
            $table->string('nationality')->default('سوداني');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->default('male');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('photo')->nullable();

            // بيانات وظيفية
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('job_title');
            $table->enum('contract_type', ['permanent', 'temporary', 'part_time', 'contract'])->default('permanent');
            $table->date('hire_date');
            $table->date('contract_end_date')->nullable();
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->enum('status', ['active', 'on_leave', 'terminated'])->default('active');

            // بيانات بنكية
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();

            // إجازات
            $table->unsignedSmallInteger('annual_leave_balance')->default(21);
            $table->unsignedSmallInteger('sick_leave_balance')->default(15);

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
