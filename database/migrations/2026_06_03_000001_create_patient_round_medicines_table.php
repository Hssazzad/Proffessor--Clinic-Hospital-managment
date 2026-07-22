<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('patient_round_medicines')) {
            Schema::create('patient_round_medicines', function (Blueprint $table) {
                $table->id();
                $table->string('patientid', 50)->nullable();
                $table->integer('admissionid')->nullable();
                $table->string('templeteid', 50)->nullable();
                $table->string('name', 255)->nullable();
                $table->string('strength', 100)->nullable();
                $table->string('dose', 100)->nullable();
                $table->string('morning', 100)->nullable();
                $table->string('noon', 100)->nullable();
                $table->string('night', 100)->nullable();
                $table->string('route', 100)->nullable();
                $table->string('duration', 100)->nullable();
                $table->string('timing', 100)->nullable();
                $table->text('instruction')->nullable();
                $table->string('order_type', 50)->nullable();
                $table->tinyInteger('active')->default(1);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_round_medicines');
    }
};
