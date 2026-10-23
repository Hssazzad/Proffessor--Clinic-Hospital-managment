<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('common_medicine', function (Blueprint $table) {
            if (!Schema::hasColumn('common_medicine', 'note')) {
                $table->string('note', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('common_medicine', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};
