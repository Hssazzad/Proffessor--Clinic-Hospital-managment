<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoice_due_adjustments')) {
            Schema::create('invoice_due_adjustments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('invoice_id')->index();
                $table->decimal('adjustment_amount', 10, 2);
                $table->string('reason', 255)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->enum('approval_status', ['approved', 'pending', 'rejected', 'reversed'])->default('pending')->index();
                $table->dateTime('reversed_at')->nullable();
                $table->unsignedBigInteger('reversed_by')->nullable();
                $table->unsignedBigInteger('reversal_of_id')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_due_adjustments');
    }
};
