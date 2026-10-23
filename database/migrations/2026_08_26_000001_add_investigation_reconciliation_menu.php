<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds "Investigation Report" to the Accounting sidebar menu (parentcode 130).
 * submenucode 130105 — method maps to the InvestigationReconciliation route.
 *
 * Grants access to every user who already has any Accounting submenu access.
 */
return new class extends Migration
{
    private const ACCOUNTING_PARENT = 130;
    private const SUB_CODE          = 130105;

    public function up(): void
    {
        // ── 1. Submenu row ───────────────────────────────────────────────────
        if (Schema::hasTable('submenu')) {
            $exists = DB::table('submenu')
                ->where('menuparentcode', self::ACCOUNTING_PARENT)
                ->where('submenucode', self::SUB_CODE)
                ->exists();

            if (! $exists) {
                DB::table('submenu')->insert([
                    'menuparentcode' => self::ACCOUNTING_PARENT,
                    'submenucode'    => self::SUB_CODE,
                    'submenuname'    => 'Investigation Report',
                    'method'         => 'InvestigationReport',
                    'position'       => 10,
                ]);
            }
        }

        // ── 2. Grant to existing Accounting users ────────────────────────────
        if (! Schema::hasTable('user_sub_menu')) {
            return;
        }

        $accountingUsers = DB::table('user_sub_menu')
            ->where('menuparentcode', self::ACCOUNTING_PARENT)
            ->pluck('userpin')
            ->unique();

        foreach ($accountingUsers as $userpin) {
            $alreadyGranted = DB::table('user_sub_menu')
                ->where('userpin', $userpin)
                ->where('menuparentcode', self::ACCOUNTING_PARENT)
                ->where('submenucode', self::SUB_CODE)
                ->exists();

            if (! $alreadyGranted) {
                DB::table('user_sub_menu')->insert([
                    'userpin'        => $userpin,
                    'menuparentcode' => self::ACCOUNTING_PARENT,
                    'submenucode'    => self::SUB_CODE,
                    'position'       => 10,
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('submenu')) {
            DB::table('submenu')
                ->where('menuparentcode', self::ACCOUNTING_PARENT)
                ->where('submenucode', self::SUB_CODE)
                ->delete();
        }

        if (Schema::hasTable('user_sub_menu')) {
            DB::table('user_sub_menu')
                ->where('menuparentcode', self::ACCOUNTING_PARENT)
                ->where('submenucode', self::SUB_CODE)
                ->delete();
        }
    }
};
