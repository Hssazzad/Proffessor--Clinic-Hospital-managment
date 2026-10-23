<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('doctors')) {
            Schema::create('doctors', function (Blueprint $table) {
                $table->id();
                $table->string('reg_no', 12)->nullable();
                $table->string('doctor_name', 50)->nullable();
                $table->string('speciality', 25)->nullable();
                $table->string('contact', 12)->nullable();
                $table->string('Posting', 25)->nullable();
                $table->string('RateCode', 12)->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('configspeciality')) {
            Schema::create('configspeciality', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('prescription_doctors')) {
            Schema::create('prescription_doctors', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('prescription_id');
                $table->unsignedBigInteger('patient_id')->nullable();
                $table->unsignedBigInteger('doctor_id');
                $table->string('role', 80)->nullable();
                $table->string('note', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_doctors');
        Schema::dropIfExists('configspeciality');
        Schema::dropIfExists('doctors');
    }
};
