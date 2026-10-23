<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('investigation_payments')) { return; }

        Schema::table('investigation_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('ref_doctor_id')->nullable()->after('CollectedBy');
            $table->string('RefDoctor')->nullable()->after('ref_doctor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('investigation_payments')) { return; }

        Schema::table('investigation_payments', function (Blueprint $table) {
            $table->dropColumn(['ref_doctor_id', 'RefDoctor']);
        });
    }
};
