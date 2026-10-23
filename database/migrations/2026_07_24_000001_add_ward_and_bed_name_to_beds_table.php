<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beds', function (Blueprint $table) {
            if (!Schema::hasColumn('beds', 'ward')) {
                $table->string('ward', 100)->nullable()->after('bed_number');
            }
            if (!Schema::hasColumn('beds', 'bed_name')) {
                $table->string('bed_name', 100)->nullable()->after('ward');
            }
        });
    }

    public function down(): void
    {
        Schema::table('beds', function (Blueprint $table) {
            $table->dropColumn(['ward', 'bed_name']);
        });
    }
};
