<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('patient_round_medicines')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `patient_round_medicines` MODIFY `order_type` VARCHAR(50) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE patient_round_medicines ALTER COLUMN order_type TYPE VARCHAR(50)');
            DB::statement('ALTER TABLE patient_round_medicines ALTER COLUMN order_type DROP NOT NULL');
        }
    }

    public function down(): void
    {
        // No-op: previous enum/length is unknown.
    }
};
