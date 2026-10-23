<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('common_medicine', function (Blueprint $table) {
            if (!Schema::hasColumn('common_medicine', 'commonly_used')) {
                $table->boolean('commonly_used')->default(false)->after('dosage_form_id');
            }
            if (!Schema::hasColumn('common_medicine', 'active')) {
                $table->boolean('active')->default(true)->after('commonly_used');
            }
        });
    }

    public function down(): void
    {
        Schema::table('common_medicine', function (Blueprint $table) {
            $table->dropColumn(['commonly_used', 'active']);
        });
    }
};
