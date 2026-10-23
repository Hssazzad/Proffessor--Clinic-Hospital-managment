<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('preconassessment_values')) {
            Schema::create('preconassessment_values', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('assessment_id')->index();
                $table->unsignedBigInteger('oe_master_id')->index();
                $table->string('value', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('preconassessment_values');
    }
};
