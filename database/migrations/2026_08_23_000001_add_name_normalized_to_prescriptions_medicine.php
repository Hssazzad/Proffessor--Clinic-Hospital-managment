<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds name_normalized to prescriptions_medicine and a composite unique index
 * (prescription_id, name_normalized, strength) that hard-guarantees one
 * medicine cannot be prescribed twice on the same prescription.
 *
 * Note: MySQL treats NULLs as distinct in unique indexes, so rows saved with a
 * NULL strength are additionally protected by the application-level fast-path
 * duplicate check (PrescriptionWizardController::ajaxMedicine / action=add).
 */
return new class extends Migration
{
    private const TABLE = 'prescriptions_medicine';
    private const INDEX = 'pm_rx_dupe_guard';

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            return;
        }

        if (!Schema::hasColumn(self::TABLE, 'name_normalized')) {
            Schema::table(self::TABLE, function (Blueprint $table) {
                $table->string('name_normalized')->nullable()->after('name');
            });
        }

        // Backfill existing rows so the unique index guards legacy data too.
        DB::table(self::TABLE)
            ->whereNull('name_normalized')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table(self::TABLE)
                        ->where('id', $row->id)
                        ->update([
                            'name_normalized' => mb_strtolower(
                                trim(preg_replace('/\s+/', ' ', (string) $row->name))
                            ),
                        ]);
                }
            });

        try {
            Schema::table(self::TABLE, function (Blueprint $table) {
                $table->unique(
                    ['prescription_id', 'name_normalized', 'strength'],
                    self::INDEX
                );
            });
        } catch (\Throwable $e) {
            // Index already exists (re-run against a migrated DB) — safe to ignore.
            if (!str_contains($e->getMessage(), 'exist') && !str_contains($e->getMessage(), 'duplicate')) {
                throw $e;
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            return;
        }

        try {
            Schema::table(self::TABLE, function (Blueprint $table) {
                $table->dropUnique(self::INDEX);
            });
        } catch (\Throwable $e) {
            // Index absent — nothing to roll back.
        }

        if (Schema::hasColumn(self::TABLE, 'name_normalized')) {
            Schema::table(self::TABLE, function (Blueprint $table) {
                $table->dropColumn('name_normalized');
            });
        }
    }
};
