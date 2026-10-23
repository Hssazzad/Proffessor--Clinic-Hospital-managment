<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('common_medicine', function (Blueprint $table) {
            $table->foreignId('medicine_type_id')
                  ->nullable()
                  ->constrained('medicine_types')
                  ->nullOnDelete()
                  ->after('company');
        });
    }

    public function down(): void
    {
        Schema::table('common_medicine', function (Blueprint $table) {
            $table->dropForeign(['medicine_type_id']);
            $table->dropColumn('medicine_type_id');
        });
    }
};
