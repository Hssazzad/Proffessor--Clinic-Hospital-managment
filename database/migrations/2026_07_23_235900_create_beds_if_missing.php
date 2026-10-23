<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `beds` table when it is missing (e.g. fresh test databases).
 * Production instances already have this table from the original dump; the
 * guard keeps them untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('beds')) {
            return;
        }

        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->string('bed_number', 50)->unique();
            $table->string('ward', 100)->nullable();
            $table->string('bed_name', 100)->nullable();
            $table->string('bed_type', 100)->nullable();
            $table->decimal('price_per_day', 10, 2)->nullable();
            $table->string('status', 30)->default('vacant');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};
