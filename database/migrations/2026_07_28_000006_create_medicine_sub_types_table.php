<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_sub_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_type_id')->constrained('medicine_types')->cascadeOnDelete();
            $table->string('name', 150);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_sub_types');
    }
};
