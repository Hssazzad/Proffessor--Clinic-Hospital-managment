<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Collection Groups — fixed bundles of chart-of-accounts heads used to fill the
 * Multiple Cash Receive screen in one shot.
 *
 * A group is only a template: picking "001" on the receive screen renders one
 * row per linked account with Amount = 0, the user types the real amounts and
 * the normal MCR save path posts them. Nothing here changes how vouchers are
 * written, so the Unauthorized List / authorization flow is untouched.
 *
 * `collection_group_accounts.account_code` stores the GL `AccountNo` (the same
 * value the account dropdown submits), NOT the `acct_tbl_balsheet_account.ID`.
 * AccountNo is a stable unique business key while IDs are surrogate, and the
 * MCR form never sees an ID. It is deliberately not a foreign key:
 * acct_tbl_balsheet_account is a legacy latin1 table that account renumbering
 * (Accounting/changeAccountNo) touches, and a hard FK would block that or
 * cascade-delete saved groups. Staleness is handled by validating AccountNo
 * against the account table on every write instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('collection_groups')) {
            Schema::create('collection_groups', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 150);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('collection_group_accounts')) {
            Schema::create('collection_group_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('collection_group_id')
                    ->constrained('collection_groups')
                    ->cascadeOnDelete();
                $table->string('account_code', 20);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['collection_group_id', 'account_code'], 'cga_group_account_unique');
                $table->index('account_code');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_group_accounts');
        Schema::dropIfExists('collection_groups');
    }
};