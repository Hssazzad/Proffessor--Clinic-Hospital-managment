<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('oe_master')) {
            Schema::create('oe_master', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('short_name', 30)->nullable();
                $table->string('unit', 20)->nullable();
                $table->string('input_type', 20);
                $table->integer('sort_order');
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('oe_master');
    }
};
