<?php

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
        Schema::create('discharge_diagnoses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        if (!Schema::hasTable('discharges')) { return; }

        Schema::table('discharges', function (Blueprint $table) {
            $table->unsignedBigInteger('discharge_diagnosis_id')->nullable()->after('diagnosis');
            $table->foreign('discharge_diagnosis_id')
                  ->references('id')
                  ->on('discharge_diagnoses')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('discharges')) { return; }

        Schema::table('discharges', function (Blueprint $table) {
            $table->dropForeign(['discharge_diagnosis_id']);
            $table->dropColumn('discharge_diagnosis_id');
        });

        Schema::dropIfExists('discharge_diagnoses');
    }
};
