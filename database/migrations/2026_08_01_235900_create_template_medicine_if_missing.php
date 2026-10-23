<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `template_medicine` table when it is missing (e.g. fresh test
 * databases). Production instances already have this table from the original
 * dump; the guard keeps them untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('template_medicine')) {
            return;
        }

        Schema::create('template_medicine', function (Blueprint $table) {
            $table->id();
            $table->string('templeteid', 50)->index();
            $table->string('group', 150)->nullable();
            $table->string('name', 255);
            $table->string('strength', 100)->nullable();
            $table->string('dose', 100)->nullable();
            $table->string('route', 100)->nullable();
            $table->string('frequency', 100)->nullable();
            $table->string('duration', 100)->nullable();
            $table->string('timing', 100)->nullable();
            $table->string('meal_timing', 100)->nullable();
            $table->string('morning', 20)->nullable();
            $table->string('noon', 20)->nullable();
            $table->string('night', 20)->nullable();
            $table->string('instruction', 255)->nullable();
            $table->string('company', 150)->nullable();
            $table->unsignedBigInteger('medicine_id')->nullable();
            $table->string('generic_name', 255)->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('order_type', 30)->default('fresh');
            $table->text('note')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_medicine');
    }
};
