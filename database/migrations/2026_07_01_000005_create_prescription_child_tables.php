<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('prescriptions_complain')) {
            Schema::create('prescriptions_complain', function (Blueprint $table) {
                $table->id();
                $table->string('patientcode', 30)->nullable()->index();
                $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
                $table->text('complaint')->nullable();
                $table->text('note')->nullable();
                $table->string('name', 255)->nullable();
                $table->boolean('active')->default(true);
                $table->string('name_normalized', 255)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('prescriptions_diagnosis')) {
            Schema::create('prescriptions_diagnosis', function (Blueprint $table) {
                $table->id();
                $table->string('patientcode', 30)->nullable()->index();
                $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
                $table->string('name', 255);
                $table->text('note')->nullable();
                $table->boolean('active')->default(true);
                $table->string('name_normalized', 255)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('prescriptions_medicine')) {
            Schema::create('prescriptions_medicine', function (Blueprint $table) {
                $table->id();
                $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
                $table->string('medicine_name', 255)->nullable();
                $table->string('name', 255);
                $table->string('strength', 100)->nullable();
                $table->string('dose', 100)->nullable();
                $table->string('route', 100)->nullable();
                $table->string('frequency', 100)->nullable();
                $table->string('duration', 100)->nullable();
                $table->string('timing', 100)->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('prescription_diagnoses')) {
            Schema::create('prescription_diagnoses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
                $table->string('name', 255);
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('prescription_medicines')) {
            Schema::create('prescription_medicines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
                $table->string('name', 255);
                $table->string('strength', 100)->nullable();
                $table->string('dose', 100)->nullable();
                $table->string('route', 100)->nullable();
                $table->string('frequency', 100)->nullable();
                $table->string('duration', 100)->nullable();
                $table->string('timing', 100)->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (['prescriptions_complain', 'prescriptions_diagnosis', 'prescriptions_medicine', 'prescription_diagnoses', 'prescription_medicines'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
