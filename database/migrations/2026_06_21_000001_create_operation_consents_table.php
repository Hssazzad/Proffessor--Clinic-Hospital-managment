<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('operation_consents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_id');
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->string('patientcode')->nullable();
            $table->string('patient_name')->nullable();
            $table->string('father_husband_name')->nullable();
            $table->string('age')->nullable();
            $table->string('village')->nullable();
            $table->string('post_office')->nullable();
            $table->string('police_station')->nullable();
            $table->string('district')->nullable();
            $table->string('ward_no')->nullable();
            $table->string('bed_no')->nullable();
            $table->string('disease_name')->nullable();
            $table->string('operation_name')->nullable();
            $table->date('operation_date')->nullable();
            $table->string('operation_time')->nullable();
            $table->string('surgeon_name')->nullable();
            $table->string('assistant_surgeon')->nullable();
            $table->string('anesthetist')->nullable();
            $table->string('anesthesiologist')->nullable();
            $table->text('operation_note')->nullable();
            $table->text('consent_text')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('relation')->nullable();
            $table->string('signatory_name')->nullable();
            $table->string('signatory_relation')->nullable();
            $table->string('doctor_signature_name')->nullable();
            $table->string('signature_patient')->nullable();
            $table->string('signature_doctor')->nullable();
            $table->string('signature_witness')->nullable();
            $table->date('consent_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('operation_consents');
    }
};
