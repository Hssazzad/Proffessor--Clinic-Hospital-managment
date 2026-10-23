<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * configMain e "Show in Investigation bill dropdown" flag.
     * getMain() endpoint + invoice frontend etai diye dynamic filter kore
     * (hardcoded ECG/X-Ray/Pathology/USG whitelist er bodole).
     */
    public function up(): void
    {
        if (!Schema::hasTable('configMain')) {
            return;
        }
        if (Schema::hasColumn('configMain', 'is_investigation_type')) {
            return;
        }
        DB::statement('ALTER TABLE configMain ADD COLUMN is_investigation_type TINYINT(1) NOT NULL DEFAULT 0 AFTER Name');
    }

    public function down(): void
    {
        if (!Schema::hasTable('configMain') || !Schema::hasColumn('configMain', 'is_investigation_type')) {
            return;
        }
        DB::statement('ALTER TABLE configMain DROP COLUMN is_investigation_type');
    }
};
