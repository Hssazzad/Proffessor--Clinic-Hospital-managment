<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Category standardization at the entry point:
 *  - rename configMain rows to canonical names (X-Ray / Pathology / USG)
 *  - add the missing "ECG" master category + its configSub services
 *  - make the Investigation bill type use the category dropdown
 *    (requires_category, no free text)
 */
return new class extends Migration
{
    private const ECG_CODE = 107;

    public function up(): void
    {
        $this->fixConfigMain();

        if (!Schema::hasTable('configSub')) {
            return;
        }

        $this->seedEcgServices();

        $this->fixBillTypes();
    }

    private function fixConfigMain(): void
    {
        if (!Schema::hasTable('configMain')) {
            return;
        }

        $renames = [
            [101, 'X Ray',        'X-Ray'],
            [103, 'PATHOLOGY',    'Pathology'],
            [104, 'ULTRASONOGR',  'USG'],
            [102, 'Admission  fee', 'Admission Fee'],
        ];

        foreach ($renames as [$code, $oldName, $newName]) {
            DB::table('configMain')
                ->where('Code', $code)
                ->where('Name', $oldName)
                ->update(['Name' => $newName]);
        }

        if (!DB::table('configMain')->where('Code', self::ECG_CODE)->exists()) {
            DB::table('configMain')->insert([
                'Code' => self::ECG_CODE,
                'Name' => 'ECG',
            ]);
        }
    }

    private function seedEcgServices(): void
    {
        $ecgServices = [
            [self::ECG_CODE, self::ECG_CODE . '01', 'ECG (12-Lead)',            250],
            [self::ECG_CODE, self::ECG_CODE . '02', 'Echo / 2D Echo',          1500],
            [self::ECG_CODE, self::ECG_CODE . '03', 'Holter Monitoring (24hr)', 3000],
        ];

        foreach ($ecgServices as [$mainCode, $code, $name, $amount]) {
            $exists = DB::table('configSub')
                ->where('MainCode', $mainCode)
                ->where('Code', $code)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('configSub')->insert([
                'MainCode' => $mainCode,
                'Code'     => $code,
                'Name'     => $name,
                'Amount'   => $amount,
            ]);
        }
    }

    private function fixBillTypes(): void
    {
        if (!Schema::hasTable('bill_types')) {
            return;
        }

        // Investigation bill type: force the category dropdown + service list,
        // disable the free-text item path so CategoryName stays canonical.
        DB::table('bill_types')
            ->where('slug', '2')
            ->where('name', 'Investigation')
            ->update([
                'requires_category' => 1,
                'free_text_items'   => 0,
            ]);
    }

    public function down(): void
    {
        // Data renames are not rolled back (idempotent, non-destructive).
    }
};
