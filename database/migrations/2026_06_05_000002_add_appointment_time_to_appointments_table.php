<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('appointments')) {
            return;
        }

        $columns = Schema::getColumnListing('appointments');

        if (!in_array('appointment_time', $columns, true)) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->time('appointment_time')->nullable()->after('appointment_date');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('appointments')) {
            return;
        }

        $columns = Schema::getColumnListing('appointments');

        if (in_array('appointment_time', $columns, true)) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('appointment_time');
            });
        }
    }
};
