<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('usg_reports', 'report_data')) {
            return;
        }

        Schema::table('usg_reports', function (Blueprint $table) {
            $table->json('report_data')->nullable()->after('findings');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('usg_reports', 'report_data')) {
            Schema::table('usg_reports', function (Blueprint $table) {
                $table->dropColumn('report_data');
            });
        }
    }
};
