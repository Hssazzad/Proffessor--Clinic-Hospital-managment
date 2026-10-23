<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('common_medicine', function (Blueprint $table) {
            if (!Schema::hasColumn('common_medicine', 'company_id')) {
                $table->foreignId('company_id')
                      ->nullable()
                      ->after('medicine_sub_type_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('common_medicine', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });
    }
};
