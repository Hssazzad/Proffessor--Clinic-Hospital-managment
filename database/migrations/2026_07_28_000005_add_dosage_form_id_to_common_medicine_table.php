<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('common_medicine', function (Blueprint $table) {
            $table->foreignId('dosage_form_id')
                  ->nullable()
                  ->constrained('dosage_forms')
                  ->nullOnDelete()
                  ->after('medicine_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('common_medicine', function (Blueprint $table) {
            $table->dropForeign(['dosage_form_id']);
            $table->dropColumn('dosage_form_id');
        });
    }
};
