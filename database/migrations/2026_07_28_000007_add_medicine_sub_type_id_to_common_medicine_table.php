<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('common_medicine', function (Blueprint $table) {
            $table->foreignId('medicine_sub_type_id')
                ->nullable()
                ->after('medicine_type_id')
                ->constrained('medicine_subtypes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('common_medicine', function (Blueprint $table) {
            $table->dropForeign(['medicine_sub_type_id']);
            $table->dropColumn('medicine_sub_type_id');
        });
    }
};
