<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('medicine_subtypes')) {
            DB::statement('CREATE TABLE "medicine_subtypes" (
                "id" integer primary key autoincrement not null,
                "medicine_type_id" integer not null,
                "name" varchar(150) not null,
                "active" tinyint(1) not null default 1,
                "created_at" datetime,
                "updated_at" datetime
            )');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_subtypes');
    }
};
