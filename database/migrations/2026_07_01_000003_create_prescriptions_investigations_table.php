<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('prescriptions_investigations')) {
            Schema::create('prescriptions_investigations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
                $table->foreignId('investigation_id')->nullable()->constrained('common_investigation')->nullOnDelete();
                $table->string('name', 150);
                $table->string('note', 255)->nullable();
                $table->timestamps();
            });
        } else {
            $cols = Schema::getColumnListing('prescriptions_investigations');

            if (in_array('prescription_id', $cols)) {
                $colType = DB::selectOne(
                    'SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND COLUMN_NAME = ?',
                    ['prescriptions_investigations', 'prescription_id']
                );

                $isString = $colType && in_array(strtolower($colType->DATA_TYPE), ['varchar', 'char', 'text']);

                if ($isString) {
                    DB::statement('ALTER TABLE prescriptions_investigations MODIFY prescription_id BIGINT UNSIGNED NOT NULL');

                    try {
                        $fkExists = DB::selectOne(
                            "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                             WHERE TABLE_NAME = 'prescriptions_investigations'
                             AND COLUMN_NAME = 'prescription_id'
                             AND REFERENCED_TABLE_NAME IS NOT NULL"
                        );

                        if (!$fkExists) {
                            DB::statement('ALTER TABLE prescriptions_investigations ADD CONSTRAINT fk_prescriptions_investigations_prescription
                                           FOREIGN KEY (prescription_id) REFERENCES prescriptions(id) ON DELETE CASCADE');
                        }
                    } catch (\Exception $e) {
                    }
                }
            }

            if (in_array('investigation_id', $cols)) {
                try {
                    $fkExists = DB::selectOne(
                        "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                         WHERE TABLE_NAME = 'prescriptions_investigations'
                         AND COLUMN_NAME = 'investigation_id'
                         AND REFERENCED_TABLE_NAME IS NOT NULL"
                    );

                    if (!$fkExists) {
                        $hasTable = Schema::hasTable('common_investigation');
                        if ($hasTable) {
                            DB::statement('ALTER TABLE prescriptions_investigations
                                           ADD CONSTRAINT fk_prescriptions_investigations_investigation
                                           FOREIGN KEY (investigation_id) REFERENCES common_investigation(id) ON DELETE SET NULL');
                        }
                    }
                } catch (\Exception $e) {
                }
            }
        }
    }

    public function down(): void
    {
    }
};
