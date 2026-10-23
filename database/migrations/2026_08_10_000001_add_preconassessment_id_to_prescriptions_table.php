<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('prescriptions') || !Schema::hasTable('preconassessment')) {
            return;
        }

        if (!Schema::hasColumn('prescriptions', 'preconassessment_id')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->unsignedBigInteger('preconassessment_id')->nullable()->after('patient_id');
                $table->foreign('preconassessment_id')
                    ->references('id')
                    ->on('preconassessment')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('prescriptions') && Schema::hasColumn('prescriptions', 'preconassessment_id')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->dropForeign(['preconassessment_id']);
                $table->dropColumn('preconassessment_id');
            });
        }
    }
};
