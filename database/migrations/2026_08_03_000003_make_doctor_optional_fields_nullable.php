<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('doctors')) { return; }

        Schema::table('doctors', function (Blueprint $table) {
            $table->string('reg_no', 12)->nullable()->change();
            $table->string('speciality', 25)->nullable()->change();
            $table->string('contact', 12)->nullable()->change();
            $table->string('Posting', 25)->nullable()->change();
            $table->string('RateCode', 12)->nullable()->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('doctors')) { return; }

        Schema::table('doctors', function (Blueprint $table) {
            $table->string('speciality', 25)->nullable(false)->change();
            $table->string('contact', 12)->nullable(false)->change();
            $table->string('Posting', 25)->nullable(false)->change();
        });
    }
};
