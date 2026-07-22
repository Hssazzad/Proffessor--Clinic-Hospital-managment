<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('patient_admission')) {
            return;
        }

        $columns = Schema::getColumnListing('patient_admission');

        Schema::table('patient_admission', function (Blueprint $table) use ($columns) {
            if (!in_array('doctor_id', $columns, true)) {
                $table->unsignedBigInteger('doctor_id')->nullable()->after('templeteid');
            }

            if (!in_array('doctor_name', $columns, true)) {
                $table->string('doctor_name', 150)->nullable()->after('doctor_id');
            }

            if (!in_array('doctor_reg_no', $columns, true)) {
                $table->string('doctor_reg_no', 60)->nullable()->after('doctor_name');
            }

            if (!in_array('department', $columns, true)) {
                $table->string('department', 120)->nullable()->after('doctor_reg_no');
            }

            if (!in_array('doctor_mobile', $columns, true)) {
                $table->string('doctor_mobile', 50)->nullable()->after('department');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('patient_admission')) {
            return;
        }

        $columns = Schema::getColumnListing('patient_admission');
        $drop = collect(['doctor_id', 'doctor_name', 'doctor_reg_no', 'department', 'doctor_mobile'])
            ->filter(fn ($column) => in_array($column, $columns, true))
            ->values()
            ->all();

        if (!empty($drop)) {
            Schema::table('patient_admission', function (Blueprint $table) use ($drop) {
                $table->dropColumn($drop);
            });
        }
    }
};
