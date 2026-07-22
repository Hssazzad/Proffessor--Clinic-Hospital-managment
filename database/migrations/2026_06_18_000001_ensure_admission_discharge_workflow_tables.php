<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('discharges')) {
            Schema::create('discharges', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('patient_id');
                $table->unsignedBigInteger('admission_id');
                $table->date('discharge_date')->nullable();
                $table->string('diagnosis', 300)->nullable();
                $table->string('doctor_name', 150)->nullable();
                $table->string('ward', 100)->nullable();
                $table->string('reg_no', 100)->nullable();
                $table->string('operation_name', 300)->nullable();
                $table->text('operation_method')->nullable();
                $table->string('surgeon', 200)->nullable();
                $table->string('anesthetist', 200)->nullable();
                $table->string('assistant_1', 200)->nullable();
                $table->string('assistant_2', 200)->nullable();
                $table->text('investigations')->nullable();
                $table->string('blood_group', 10)->nullable();
                $table->string('drug_allergy', 200)->nullable();
                $table->text('notes')->nullable();
                $table->date('follow_up_date')->nullable();
                $table->enum('status', ['pending', 'completed'])->default('completed');
                $table->unsignedBigInteger('discharged_by')->nullable();
                $table->timestamps();

                $table->index('patient_id');
                $table->index('admission_id');
            });
        } else {
            $columns = Schema::getColumnListing('discharges');

            Schema::table('discharges', function (Blueprint $table) use ($columns) {
                if (!in_array('patient_id', $columns, true)) {
                    $table->unsignedBigInteger('patient_id')->nullable()->index();
                }
                if (!in_array('admission_id', $columns, true)) {
                    $table->unsignedBigInteger('admission_id')->nullable()->index();
                }
                if (!in_array('discharge_date', $columns, true)) {
                    $table->date('discharge_date')->nullable();
                }
                if (!in_array('diagnosis', $columns, true)) {
                    $table->string('diagnosis', 300)->nullable();
                }
                if (!in_array('doctor_name', $columns, true)) {
                    $table->string('doctor_name', 150)->nullable();
                }
                if (!in_array('ward', $columns, true)) {
                    $table->string('ward', 100)->nullable();
                }
                if (!in_array('reg_no', $columns, true)) {
                    $table->string('reg_no', 100)->nullable();
                }
                if (!in_array('operation_name', $columns, true)) {
                    $table->string('operation_name', 300)->nullable();
                }
                if (!in_array('operation_method', $columns, true)) {
                    $table->text('operation_method')->nullable();
                }
                if (!in_array('surgeon', $columns, true)) {
                    $table->string('surgeon', 200)->nullable();
                }
                if (!in_array('anesthetist', $columns, true)) {
                    $table->string('anesthetist', 200)->nullable();
                }
                if (!in_array('assistant_1', $columns, true)) {
                    $table->string('assistant_1', 200)->nullable();
                }
                if (!in_array('assistant_2', $columns, true)) {
                    $table->string('assistant_2', 200)->nullable();
                }
                if (!in_array('investigations', $columns, true)) {
                    $table->text('investigations')->nullable();
                }
                if (!in_array('blood_group', $columns, true)) {
                    $table->string('blood_group', 10)->nullable();
                }
                if (!in_array('drug_allergy', $columns, true)) {
                    $table->string('drug_allergy', 200)->nullable();
                }
                if (!in_array('notes', $columns, true)) {
                    $table->text('notes')->nullable();
                }
                if (!in_array('follow_up_date', $columns, true)) {
                    $table->date('follow_up_date')->nullable();
                }
                if (!in_array('status', $columns, true)) {
                    $table->enum('status', ['pending', 'completed'])->default('completed');
                }
                if (!in_array('discharged_by', $columns, true)) {
                    $table->unsignedBigInteger('discharged_by')->nullable();
                }
            });
        }

        if (!Schema::hasTable('discharge_medicines')) {
            Schema::create('discharge_medicines', function (Blueprint $table) {
                $table->id();
                $table->string('patientcode', 50)->nullable();
                $table->unsignedBigInteger('patient_id')->nullable();
                $table->unsignedBigInteger('admission_id')->nullable();
                $table->string('medicine_name', 255)->nullable();
                $table->string('strength', 100)->nullable();
                $table->string('dose', 100)->nullable();
                $table->string('morning', 100)->nullable();
                $table->string('noon', 100)->nullable();
                $table->string('night', 100)->nullable();
                $table->timestamps();

                $table->index('patientcode');
                $table->index('admission_id');
            });
        } else {
            $columns = Schema::getColumnListing('discharge_medicines');

            Schema::table('discharge_medicines', function (Blueprint $table) use ($columns) {
                if (!in_array('patientcode', $columns, true)) {
                    $table->string('patientcode', 50)->nullable()->index();
                }
                if (!in_array('patient_id', $columns, true)) {
                    $table->unsignedBigInteger('patient_id')->nullable();
                }
                if (!in_array('admission_id', $columns, true)) {
                    $table->unsignedBigInteger('admission_id')->nullable()->index();
                }
                if (!in_array('medicine_name', $columns, true)) {
                    $table->string('medicine_name', 255)->nullable();
                }
                if (!in_array('strength', $columns, true)) {
                    $table->string('strength', 100)->nullable();
                }
                if (!in_array('dose', $columns, true)) {
                    $table->string('dose', 100)->nullable();
                }
                if (!in_array('morning', $columns, true)) {
                    $table->string('morning', 100)->nullable();
                }
                if (!in_array('noon', $columns, true)) {
                    $table->string('noon', 100)->nullable();
                }
                if (!in_array('night', $columns, true)) {
                    $table->string('night', 100)->nullable();
                }
                if (!in_array('created_at', $columns, true) && !in_array('updated_at', $columns, true)) {
                    $table->timestamps();
                }
            });
        }
    }

    public function down(): void
    {
        // Intentionally left non-destructive for existing clinic data.
    }
};
