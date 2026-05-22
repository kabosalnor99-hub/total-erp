<?php

// المسار: database/migrations/2024_01_08_000002_create_notifications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('Target user');
            $table->string('type')->comment('Notification type: low_stock, overdue_invoice, leave_request, payroll, system');
            $table->string('title_ar')->comment('Arabic title');
            $table->string('title_en')->comment('English title');
            $table->text('body_ar')->comment('Arabic body');
            $table->text('body_en')->comment('English body');
            $table->string('icon')->default('bell')->comment('Icon name');
            $table->string('color')->default('blue')->comment('Badge color');
            $table->string('url')->nullable()->comment('Action URL');
            $table->json('data')->nullable()->comment('Additional data payload');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
