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
        Schema::create('discharge_instructions', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        if (!Schema::hasTable('discharges')) { return; }

        Schema::table('discharges', function (Blueprint $table) {
            $table->unsignedBigInteger('discharge_instruction_id')->nullable()->after('notes');
            $table->foreign('discharge_instruction_id')
                  ->references('id')
                  ->on('discharge_instructions')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('discharges')) { return; }

        Schema::table('discharges', function (Blueprint $table) {
            $table->dropForeign(['discharge_instruction_id']);
            $table->dropColumn('discharge_instruction_id');
        });

        Schema::dropIfExists('discharge_instructions');
    }
};
