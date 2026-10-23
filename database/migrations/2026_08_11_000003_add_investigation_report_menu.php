<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sidebar menu entry for the Investigation Revenue & Patient Report
 * under the Billing parent (80), granted to the users who already
 * have Billing access (user_sub_menu).
 */
return new class extends Migration
{
    private const BILLING_PARENT  = 80;
    private const REPORT_SUB_CODE = 80107;

    public function up(): void
    {
        if (Schema::hasTable('submenu')) {
            $exists = DB::table('submenu')
                ->where('menuparentcode', self::BILLING_PARENT)
                ->where('submenucode', self::REPORT_SUB_CODE)
                ->exists();

            if (! $exists) {
                DB::table('submenu')->insert([
                    'menuparentcode' => self::BILLING_PARENT,
                    'submenucode'    => self::REPORT_SUB_CODE,
                    'submenuname'    => 'Investigation Report',
                    'method'         => 'InvestigationReport',
                    'position'       => 5,
                ]);
            }
        }

        if (! Schema::hasTable('user_sub_menu')) {
            return;
        }

        $billingUsers = DB::table('user_sub_menu')
            ->where('menuparentcode', self::BILLING_PARENT)
            ->pluck('userpin')
            ->unique();

        foreach ($billingUsers as $userpin) {
            $granted = DB::table('user_sub_menu')
                ->where('userpin', $userpin)
                ->where('menuparentcode', self::BILLING_PARENT)
                ->where('submenucode', self::REPORT_SUB_CODE)
                ->exists();

            if (! $granted) {
                DB::table('user_sub_menu')->insert([
                    'userpin'        => $userpin,
                    'menuparentcode' => self::BILLING_PARENT,
                    'submenucode'    => self::REPORT_SUB_CODE,
                    'position'       => 30,
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('submenu')) {
            DB::table('submenu')
                ->where('menuparentcode', self::BILLING_PARENT)
                ->where('submenucode', self::REPORT_SUB_CODE)
                ->delete();
        }

        if (Schema::hasTable('user_sub_menu')) {
            DB::table('user_sub_menu')
                ->where('menuparentcode', self::BILLING_PARENT)
                ->where('submenucode', self::REPORT_SUB_CODE)
                ->delete();
        }
    }
};