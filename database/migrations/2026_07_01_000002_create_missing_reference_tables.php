<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('common_medicine')) {
            Schema::create('common_medicine', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->nullable()->unique();
                $table->string('name', 255);
                $table->string('GroupName', 150)->nullable();
                $table->string('company', 150)->nullable();
                $table->string('strength', 100)->nullable();
                $table->string('name_normalized', 255)->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('common_complain')) {
            Schema::create('common_complain', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->string('name_normalized', 255)->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('common_investigation')) {
            Schema::create('common_investigation', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->string('category', 150)->nullable();
                $table->text('description')->nullable();
                $table->boolean('active')->default(true);
            });
        }

        if (!Schema::hasTable('common_diagnosis')) {
            Schema::create('common_diagnosis', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->nullable()->unique();
                $table->string('name', 255);
                $table->string('name_normalized', 255)->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('preconassessment')) {
            Schema::create('preconassessment', function (Blueprint $table) {
                $table->id();
                $table->string('patientcode', 30)->index();
                $table->decimal('weight', 6, 2)->nullable();
                $table->decimal('height', 6, 2)->nullable();
                $table->decimal('temp', 5, 2)->nullable();
                $table->integer('bp_sys')->nullable();
                $table->integer('bp_dia')->nullable();
                $table->integer('pulse')->nullable();
                $table->integer('spo2')->nullable();
                $table->integer('rr')->nullable();
                $table->text('notes')->nullable();
                $table->string('code', 50)->nullable();
                $table->string('value', 255)->nullable();
                $table->timestamps();

                $table->foreign('patientcode')->references('patientcode')->on('patients')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['common_medicine', 'common_complain', 'common_investigation', 'common_diagnosis', 'preconassessment'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
