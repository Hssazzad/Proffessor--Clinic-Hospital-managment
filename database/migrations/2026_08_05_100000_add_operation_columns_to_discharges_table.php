<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('discharges')) { return; }

        Schema::table('discharges', function (Blueprint $table) {
            $table->unsignedBigInteger('operation_name_id')->nullable();
            $table->unsignedBigInteger('surgeon_id')->nullable();
            $table->unsignedBigInteger('anesthesiologist_id')->nullable();
            $table->unsignedBigInteger('assistant_surgeon_1_id')->nullable();
            $table->unsignedBigInteger('assistant_surgeon_2_id')->nullable();
            $table->date('operation_date')->nullable();
            $table->time('operation_time')->nullable();
            $table->string('anesthesia_type', 100)->nullable();

            $table->index('operation_name_id');
            $table->index('surgeon_id');
            $table->index('anesthesiologist_id');
            $table->index('assistant_surgeon_1_id');
            $table->index('assistant_surgeon_2_id');

            $table->foreign('operation_name_id')->references('id')->on('operation_names')->onDelete('set null');
            $table->foreign('surgeon_id')->references('id')->on('doctors')->onDelete('set null');
            $table->foreign('anesthesiologist_id')->references('id')->on('anesthesiologists')->onDelete('set null');
            $table->foreign('assistant_surgeon_1_id')->references('id')->on('assistant_surgeons')->onDelete('set null');
            $table->foreign('assistant_surgeon_2_id')->references('id')->on('assistant_surgeons')->onDelete('set null');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('discharges')) { return; }

        Schema::table('discharges', function (Blueprint $table) {
            $table->dropForeign(['operation_name_id']);
            $table->dropForeign(['surgeon_id']);
            $table->dropForeign(['anesthesiologist_id']);
            $table->dropForeign(['assistant_surgeon_1_id']);
            $table->dropForeign(['assistant_surgeon_2_id']);

            $table->dropIndex(['operation_name_id']);
            $table->dropIndex(['surgeon_id']);
            $table->dropIndex(['anesthesiologist_id']);
            $table->dropIndex(['assistant_surgeon_1_id']);
            $table->dropIndex(['assistant_surgeon_2_id']);

            $table->dropColumn([
                'operation_name_id',
                'surgeon_id',
                'anesthesiologist_id',
                'assistant_surgeon_1_id',
                'assistant_surgeon_2_id',
                'operation_date',
                'operation_time',
                'anesthesia_type',
            ]);
        });
    }
};
