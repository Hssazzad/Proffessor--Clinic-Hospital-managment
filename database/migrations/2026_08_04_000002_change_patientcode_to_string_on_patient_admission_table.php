<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * patient_admission.patientcode was created as BIGINT UNSIGNED on the live
 * schema, but patient codes are alphanumeric (e.g. "P20251025-0001",
 * "TST9999"). Any admit with a non-numeric code fails with
 * "SQLSTATE[22007] Incorrect integer value" on insert. Align the column with
 * patients.patientcode (varchar).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' || !Schema::hasTable('patient_admission')) {
            return; // MODIFY is MySQL-only; sqlite tests don't need strict typing.
        }

        DB::statement('ALTER TABLE patient_admission MODIFY patientcode VARCHAR(30) NOT NULL');
    }

    public function down(): void
    {
        // Intentionally non-destructive: reverting to BIGINT would truncate
        // any alphanumeric codes written while this migration was in effect.
    }
};
