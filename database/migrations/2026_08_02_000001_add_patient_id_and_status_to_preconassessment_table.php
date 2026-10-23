<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('preconassessment')) {
            return;
        }

        if (!Schema::hasColumn('preconassessment', 'patient_id')) {
            Schema::table('preconassessment', function (Blueprint $table) {
                $table->unsignedBigInteger('patient_id')->nullable()->after('patientcode');
            });
        }

        if (!Schema::hasColumn('preconassessment', 'status')) {
            Schema::table('preconassessment', function (Blueprint $table) {
                $table->string('status', 30)->nullable()->after('notes');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('preconassessment')) {
            return;
        }

        if (Schema::hasColumn('preconassessment', 'status')) {
            Schema::table('preconassessment', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasColumn('preconassessment', 'patient_id')) {
            Schema::table('preconassessment', function (Blueprint $table) {
                $table->dropColumn('patient_id');
            });
        }
    }
};
