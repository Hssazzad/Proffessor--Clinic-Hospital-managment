<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('doctors', 'user_id')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('RateCode');
            });
        }

        if (!Schema::hasTable('usg_reports')) {
            Schema::create('usg_reports', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('patient_id')->index();
                $table->string('patientcode', 30)->index();
                $table->unsignedBigInteger('doctor_id')->index();
                $table->date('report_date');
                $table->longText('findings');
                $table->text('impression')->nullable();
                $table->date('followup_date')->nullable()->index();
                $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending')->index();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
                $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('usg_followups')) {
            Schema::create('usg_followups', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('usg_report_id')->index();
                $table->unsignedBigInteger('patient_id')->index();
                $table->unsignedBigInteger('doctor_id')->index();
                $table->date('followup_date')->index();
                $table->enum('status', ['pending', 'contacted', 'completed', 'cancelled'])->default('pending')->index();
                $table->dateTime('contacted_at')->nullable();
                $table->unsignedBigInteger('contacted_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('usg_report_id')->references('id')->on('usg_reports')->onDelete('cascade');
                $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
                $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
                $table->foreign('contacted_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('doctor_notifications')) {
            Schema::create('doctor_notifications', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('doctor_id')->index();
                $table->unsignedBigInteger('usg_followup_id')->index();
                $table->string('type', 50)->default('usg_followup');
                $table->string('title', 190);
                $table->text('message')->nullable();
                $table->boolean('is_read')->default(false)->index();
                $table->dateTime('notified_at')->nullable();
                $table->timestamps();

                $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
                $table->foreign('usg_followup_id')->references('id')->on('usg_followups')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_notifications');
        Schema::dropIfExists('usg_followups');
        Schema::dropIfExists('usg_reports');

        if (Schema::hasColumn('doctors', 'user_id')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }
};
