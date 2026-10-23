<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One-time data cleanup: map dirty CategoryName/CategoryCode values on
 * investigation_payment_items to the canonical diagnostic set
 * (ECG / X-Ray / Pathology / USG). Non-diagnostic rows
 * (Doctor Consultation, Admission fee, Blood draw matching, Others, ...)
 * are left untouched — they surface under the "Others" bucket in reports.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('investigation_payment_items')) {
            return;
        }

        if (!Schema::hasColumn('investigation_payment_items', 'CategoryName')) {
            return;
        }

        // 1) Name-based mapping first (rows whose CategoryCode is null/unknown).
        $nameMap = [
            'X RAY'         => ['X-Ray',     101],
            'X-RAY'         => ['X-Ray',     101],
            'PATHOLOGY'     => ['Pathology', 103],
            'ULTRASONOGR'   => ['USG',       104],
            'ULTRASONOGRAM' => ['USG',       104],
            'USG'           => ['USG',       104],
            'ECG'           => ['ECG',       107],
        ];

        foreach ($nameMap as $raw => [$name, $code]) {
            DB::table('investigation_payment_items')
                ->whereRaw('UPPER(TRIM(CategoryName)) = ?', [$raw])
                ->update([
                    'CategoryName' => $name,
                    'CategoryCode' => $code,
                ]);
        }

        // 2) Code-based mapping (normalises display name for already-coded rows).
        $codeMap = [
            101 => 'X-Ray',
            103 => 'Pathology',
            104 => 'USG',
        ];

        foreach ($codeMap as $code => $name) {
            DB::table('investigation_payment_items')
                ->where('CategoryCode', $code)
                ->update(['CategoryName' => $name]);
        }
    }

    public function down(): void
    {
        // Irreversible data cleanup — nothing to restore.
    }
};
