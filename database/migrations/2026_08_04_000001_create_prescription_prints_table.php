<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates an immutable snapshot table that records every prescription print
     * for audit trail, reprint, and legal compliance purposes.
     */
    public function up(): void
    {
        Schema::create('prescription_prints', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Immutable snapshot lineage: who the print belongs to.
            // Individually indexed for fast lookups by patient or admission.
            $table->string('patientcode', 50)->index();
            $table->string('admissionid', 50)->index();

            // Prescription type this print was generated from.
            // Values: admit, pre, post, round, fresh.
            $table->string('order_type', 30);

            // Current admission stage at print time (1-6).
            $table->unsignedTinyInteger('stage');

            // JSON snapshots captured at the exact moment of printing so the
            // printed document can be reproduced byte-for-byte on reprint.
            $table->json('medicines_snapshot');       // full list of medicines
            $table->json('patient_snapshot');         // name, code, mobile, age, gender, etc.
            $table->json('doctor_snapshot');          // name, speciality, reg_no, contact
            $table->json('clinical_snapshot');        // chief complaints, on examination, investigations

            // SHA-256 hash of the concatenated snapshots to guarantee the stored
            // record has not been tampered with since the print was made.
            $table->string('print_hash', 64);

            // Audit metadata: who printed, when, and from where.
            $table->unsignedBigInteger('printed_by');
            $table->timestamp('printed_at');
            $table->string('ip_address', 45)->nullable();

            // Laravel standard timestamps for record creation/update.
            $table->timestamps();

            // Composite index for the common "all prints for a patient admission
            // of a given order type" query.
            $table->index(['patientcode', 'admissionid', 'order_type']);

            // Restrict deletion: a print record cannot survive a user being deleted.
            $table->foreign('printed_by')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_prints');
    }
};