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

        if (!in_array('procedure_type_id', $columns, true)) {
            Schema::table('patient_admission', function (Blueprint $table) {
                $table->unsignedBigInteger('procedure_type_id')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('patient_admission')) {
            return;
        }

        if (in_array('procedure_type_id', Schema::getColumnListing('patient_admission'), true)) {
            Schema::table('patient_admission', function (Blueprint $table) {
                $table->dropColumn('procedure_type_id');
            });
        }
    }
};
