<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('discharge_medicines')) { return; }

        Schema::table('discharge_medicines', function (Blueprint $table) {
            $columns = Schema::getColumnListing('discharge_medicines');

            if (!in_array('timing', $columns, true)) {
                $table->string('timing', 100)->nullable()->after('night');
            }
            if (!in_array('days', $columns, true)) {
                $table->string('days', 50)->nullable()->after('timing');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('discharge_medicines')) { return; }

        Schema::table('discharge_medicines', function (Blueprint $table) {
            $table->dropColumn(['timing', 'days']);
        });
    }
};
