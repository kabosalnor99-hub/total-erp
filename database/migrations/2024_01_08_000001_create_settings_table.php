<?php

// المسار: database/migrations/2024_01_08_000001_create_settings_table.php

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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Setting key identifier');
            $table->text('value')->nullable()->comment('Setting value');
            $table->string('type')->default('string')->comment('Value type: string, integer, boolean, json, file');
            $table->string('group')->default('general')->comment('Setting group: general, company, invoice, pos, hr, accounting, notifications');
            $table->string('label_ar')->comment('Arabic label');
            $table->string('label_en')->comment('English label');
            $table->text('description_ar')->nullable()->comment('Arabic description');
            $table->text('description_en')->nullable()->comment('English description');
            $table->boolean('is_public')->default(false)->comment('Visible to all roles');
            $table->boolean('is_editable')->default(true)->comment('Can be edited from UI');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
