<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('prescription_oe')) {
            Schema::create('prescription_oe', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('prescription_id')->index();
                $table->unsignedBigInteger('oe_master_id')->index();
                $table->string('value', 255)->nullable();
                $table->timestamps();

                $table->unique(['prescription_id', 'oe_master_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_oe');
    }
};
