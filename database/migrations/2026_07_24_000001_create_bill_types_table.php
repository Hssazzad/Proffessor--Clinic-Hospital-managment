<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('color')->nullable()->default('#666');
            $table->integer('main_code')->nullable();
            $table->boolean('requires_doctor')->default(false);
            $table->boolean('requires_category')->default(false);
            $table->boolean('free_text_items')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Default data
        DB::table('bill_types')->insert([
            [
                'slug'              => 'doctor_visit',
                'name'              => 'Doctor Visit',
                'icon'              => 'fa-user-md',
                'color'             => '#2563eb',
                'main_code'         => null,
                'requires_doctor'   => 1,
                'requires_category' => 0,
                'free_text_items'   => 0,
                'is_active'         => 1,
                'sort_order'        => 1,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'slug'              => 'investigation',
                'name'              => 'Investigation',
                'icon'              => 'fa-microscope',
                'color'             => '#059669',
                'main_code'         => null,
                'requires_doctor'   => 0,
                'requires_category' => 1,
                'free_text_items'   => 0,
                'is_active'         => 1,
                'sort_order'        => 2,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'slug'              => 'full_bill',
                'name'              => 'Full Bill',
                'icon'              => 'fa-file-invoice',
                'color'             => '#dc2626',
                'main_code'         => null,
                'requires_doctor'   => 0,
                'requires_category' => 0,
                'free_text_items'   => 1,
                'is_active'         => 1,
                'sort_order'        => 3,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_types');
    }
};
