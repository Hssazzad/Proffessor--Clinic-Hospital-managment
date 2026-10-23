<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('template_medicine')) { return; }

        Schema::table('template_medicine', function (Blueprint $table) {
            if (!Schema::hasColumn('template_medicine', 'medicine_id')) {
                $table->unsignedBigInteger('medicine_id')->nullable()->after('name');
            }
            if (!Schema::hasColumn('template_medicine', 'generic_name')) {
                $table->string('generic_name', 255)->nullable()->after('medicine_id');
            }
            if (!Schema::hasColumn('template_medicine', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('company');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('template_medicine')) { return; }

        Schema::table('template_medicine', function (Blueprint $table) {
            $table->dropColumn(['medicine_id', 'generic_name', 'company_id']);
        });
    }
};
