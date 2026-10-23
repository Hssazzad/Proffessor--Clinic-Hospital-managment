<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates `patient_admission` when missing (fresh test databases).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('patient_admission')) {
            return;
        }

        Schema::create('patient_admission', function (Blueprint $table) {
            $table->id();
            $table->string('patientcode', 30);
            $table->string('patientname', 150)->nullable();
            $table->dateTime('admission_datetime')->nullable();
            $table->string('bed_no', 50)->nullable();
            $table->string('templeteid', 50)->nullable();
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->string('status', 30)->default('admitted');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_admission');
    }
};
