<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columns that must hold multiple master IDs (comma separated) for
     * multi-select discharge dropdowns. FK constraints are dropped because
     * a single FK can no longer represent the joined value.
     */
    private array $columns = [
        'operation_name_id',
        'surgeon_id',
        'anesthesiologist_id',
        'assistant_surgeon_1_id',
        'assistant_surgeon_2_id',
        'discharge_diagnosis_id',
        'discharge_instruction_id',
        'operation_method_id',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('discharges')) {
            return;
        }

        // MySQL-specific: drop FK constraints then MODIFY columns to VARCHAR(255)
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->columns as $column) {
            DB::statement("
                SELECT COUNT(*) INTO @fk_exists
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'discharges'
                  AND COLUMN_NAME = '{$column}'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            DB::statement("SET @drop_fk = IF(@fk_exists > 0,
                CONCAT('ALTER TABLE discharges DROP FOREIGN KEY discharges_{$column}_foreign'),
                'SELECT 1'
            )");
            DB::statement('PREPARE stmt FROM @drop_fk');
            DB::statement('EXECUTE stmt');
            DB::statement('DEALLOCATE PREPARE stmt');
        }

        $existing = Schema::getColumnListing('discharges');
        $modifies = [];
        foreach ($this->columns as $column) {
            if (in_array($column, $existing, true)) {
                $modifies[] = "MODIFY `{$column}` VARCHAR(255) NULL";
            }
        }

        if (!empty($modifies)) {
            DB::statement('ALTER TABLE discharges ' . implode(', ', $modifies));
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive: converting back to single bigint IDs
        // would destroy stored multi-select data for existing clinic records.
    }
};
