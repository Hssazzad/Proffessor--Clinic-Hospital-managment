<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('usg_reports', 'usg_type')) {
            return;
        }

        Schema::table('usg_reports', function (Blueprint $table) {
            $table->string('usg_type')->nullable()->after('doctor_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('usg_reports', 'usg_type')) {
            Schema::table('usg_reports', function (Blueprint $table) {
                $table->dropColumn('usg_type');
            });
        }
    }
};
