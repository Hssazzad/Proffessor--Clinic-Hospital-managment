<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addConstraintIfMissing('operation_consents', 'admission_id', 'patient_admission', 'id');
        $this->addConstraintIfMissing('discharges', 'patient_id', 'patients', 'id');
        $this->addConstraintIfMissing('discharges', 'admission_id', 'patient_admission', 'id');
        $this->addConstraintIfMissing('discharges', 'discharged_by', 'users', 'id');
        $this->addConstraintIfMissing('patient_round_medicines', 'admissionid', 'patient_admission', 'id');
        $this->addConstraintIfMissing('patient_admission', 'doctor_id', 'doctors', 'id');
        $this->addConstraintIfMissing('operation_consents', 'created_by', 'users', 'id');
    }

    public function down(): void
    {
    }

    private function addConstraintIfMissing(string $table, string $column, string $references, string $referencesColumn = 'id'): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $cols = Schema::getColumnListing($table);
        if (!in_array($column, $cols, true)) {
            return;
        }

        try {
            $fkExists = DB::selectOne(
                "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                 WHERE TABLE_NAME = ?
                 AND COLUMN_NAME = ?
                 AND REFERENCED_TABLE_NAME IS NOT NULL
                 AND TABLE_SCHEMA = DATABASE()",
                [$table, $column]
            );

            if (!$fkExists) {
                DB::statement("ALTER TABLE {$table}
                    ADD CONSTRAINT fk_{$table}_{$column}
                    FOREIGN KEY ({$column})
                    REFERENCES {$references}({$referencesColumn})
                    ON DELETE SET NULL");
            }
        } catch (\Exception $e) {
        }
    }
};
