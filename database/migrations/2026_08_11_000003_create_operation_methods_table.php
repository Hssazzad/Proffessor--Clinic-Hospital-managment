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
        Schema::create('operation_methods', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        if (!Schema::hasTable('discharges')) { return; }

        Schema::table('discharges', function (Blueprint $table) {
            $table->unsignedBigInteger('operation_method_id')->nullable()->after('operation_method');
            $table->foreign('operation_method_id')
                  ->references('id')
                  ->on('operation_methods')
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
            $table->dropForeign(['operation_method_id']);
            $table->dropColumn('operation_method_id');
        });

        Schema::dropIfExists('operation_methods');
    }
};
