<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance indexes for the medicine-add flow:
 *  - common_medicine.name_normalized   → autocomplete duplicate checks / LIKE lookups
 *  - common_medicine.commonly_used     → commonly-used panel fetch
 *  - prescriptions_medicine.prescription_id → prescribed-list loads per prescription
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('common_medicine')) {
            $this->addIndexIfMissing('common_medicine', 'name_normalized', ['name_normalized'], 'cm_name_norm_idx');
            if (Schema::hasColumn('common_medicine', 'commonly_used')) {
                $this->addIndexIfMissing('common_medicine', 'commonly_used', ['commonly_used', 'active', 'name'], 'cm_common_idx');
            }
        }

        if (Schema::hasTable('prescriptions_medicine')) {
            $this->addIndexIfMissing('prescriptions_medicine', 'prescription_id', ['prescription_id'], 'pm_prescription_idx');
        }
    }

    private function addIndexIfMissing(string $table, string $probeColumn, array $columns, string $indexName): void
    {
        if (!Schema::hasColumn($table, $probeColumn)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                $t->index($columns, $indexName);
            });
        } catch (\Throwable $e) {
            // Index already exists (re-run) — safe to ignore.
            if (!str_contains($e->getMessage(), 'exist') && !str_contains($e->getMessage(), 'duplicate')) {
                throw $e;
            }
        }
    }

    public function down(): void
    {
        foreach (
            [
                'common_medicine'        => [['cm_name_norm_idx'], ['cm_common_idx']],
                'prescriptions_medicine' => [['pm_prescription_idx']],
            ] as $table => $indexes
        ) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            foreach ($indexes[0] as $indexName) {
                try {
                    Schema::table($table, function (Blueprint $t) use ($indexName) {
                        $t->dropIndex($indexName);
                    });
                } catch (\Throwable $e) {
                    // Index absent — nothing to roll back.
                }
            }
        }
    }
};
