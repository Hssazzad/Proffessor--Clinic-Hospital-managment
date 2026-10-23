<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        foreach (['prescriptions', 'prescription_items'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $cols = Schema::getColumnListing($table);

            $isStub = count($cols) <= 3
                && in_array('id', $cols)
                && in_array('created_at', $cols)
                && in_array('updated_at', $cols);

            if ($isStub) {
                Schema::drop($table);
            }
        }
    }

    public function down(): void
    {
    }
};
