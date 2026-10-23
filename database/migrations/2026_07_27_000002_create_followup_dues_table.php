<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('followup_dues')) {
            Schema::create('followup_dues', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('admission_id')->index();
                $table->string('patientcode', 100)->index();
                $table->unsignedBigInteger('procedure_type_id')->nullable();
                $table->date('admission_date');
                $table->date('due_deadline');
                $table->decimal('amount', 10, 2)->default(100.00);
                $table->enum('status', ['pending', 'paid'])->default('pending')->index();
                $table->decimal('paid_amount', 10, 2)->nullable();
                $table->dateTime('resolved_at')->nullable();
                $table->unsignedBigInteger('resolved_by')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('procedure_types')) {
            $columns = Schema::getColumnListing('procedure_types');
            if (!in_array('track_followup_due', $columns, true)) {
                Schema::table('procedure_types', function (Blueprint $table) {
                    $table->boolean('track_followup_due')->default(0)->after('status');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('followup_dues');

        if (Schema::hasTable('procedure_types')) {
            $columns = Schema::getColumnListing('procedure_types');
            if (in_array('track_followup_due', $columns, true)) {
                Schema::table('procedure_types', function (Blueprint $table) {
                    $table->dropColumn('track_followup_due');
                });
            }
        }
    }
};
