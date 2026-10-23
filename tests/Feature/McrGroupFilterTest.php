<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\CollectionGroup;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * A group selection must expose ONLY that group's accounts.
 *
 * Regression test for: after selecting a 1-account group the page still showed a
 * manual row whose account Select2 offered the entire chart of accounts, because
 * removeBlankManualRows() refused to drop the lone blank row (guard `length > 1`).
 */
class McrGroupFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.database', 'profclinic');
    }

    private function makeGroup(string $code, array $accountCodes): CollectionGroup
    {
        $group = CollectionGroup::where('code', $code)->first()
            ?? CollectionGroup::create(['code' => $code, 'name' => 'Group ' . $code, 'is_active' => 1]);
        $group->update(['is_active' => 1]);
        $group->accounts()->delete();
        foreach (array_values($accountCodes) as $i => $accountCode) {
            $group->accounts()->create(['account_code' => $accountCode, 'sort_order' => $i + 1]);
        }
        return $group;
    }

    /** Decode the group -> accounts payload the page hands to its JS. */
    private function payload(string $html): array
    {
        $start = strpos($html, 'const groupAccounts = ') + strlen('const groupAccounts = ');
        $this->assertNotFalse($start - strlen('const groupAccounts = '), 'payload missing');
        return json_decode(substr($html, $start, strpos($html, ';', $start) - $start), true);
    }

    public function test_one_account_group_exposes_exactly_one_account()
    {
        DB::beginTransaction();
        try {
            $group = $this->makeGroup('F1', ['40601101']);

            $html = $this->actingAs(User::first())
                ->get('/Transaction/multipleCashReceive')->assertStatus(200)->getContent();

            $payload = $this->payload($html);
            $accounts = $payload[(string) $group->id];

            $this->assertCount(1, $accounts, 'a 1-account group must expose exactly 1 account');
            $this->assertSame('40601101', $accounts[0]['code']);

            // The full chart of accounts must NOT be reachable through the group.
            $allCodes = DB::table('acct_tbl_balsheet_account')
                ->where('AccountNo', '!=', config('accounting.cash_account'))
                ->pluck('AccountNo')->map('strval')->all();

            $this->assertGreaterThan(1, count($allCodes), 'fixture assumes a multi-account chart');
            $this->assertNotEquals($allCodes, array_column($accounts, 'code'));
        } finally {
            DB::rollBack();
        }
    }

    public function test_three_account_group_exposes_exactly_three_accounts_in_sort_order()
    {
        DB::beginTransaction();
        try {
            $group = $this->makeGroup('F3', ['51501101', '40601101', '50101103']);

            $html = $this->actingAs(User::first())
                ->get('/Transaction/multipleCashReceive')->getContent();

            $accounts = $this->payload($html)[(string) $group->id];

            $this->assertCount(3, $accounts);
            $this->assertSame(
                ['51501101', '40601101', '50101103'],
                array_column($accounts, 'code'),
                'payload must follow sort_order'
            );
        } finally {
            DB::rollBack();
        }
    }

    public function test_group_row_template_never_renders_an_account_select()
    {
        DB::beginTransaction();
        try {
            $this->makeGroup('F1', ['40601101']);

            $html = $this->actingAs(User::first())
                ->get('/Transaction/multipleCashReceive')->getContent();

            // The group row template must have no <select> at all.
            $this->assertMatchesRegularExpression(
                '/<template id="groupRowTemplate">(?:(?!<\/template>).)*<\/template>/s',
                $html
            );
            preg_match('/<template id="groupRowTemplate">(?:(?!<\/template>).)*<\/template>/s', $html, $m);
            $this->assertStringNotContainsString('<select', $m[0]);
            $this->assertStringContainsString('type="hidden" name="rows[__INDEX__][CreditAccount]"', $m[0]);

            // Manual rows are the only place a full-list select exists.
            preg_match('/<template id="rowTemplate">(?:(?!<\/template>).)*<\/template>/s', $html, $r);
            $this->assertStringContainsString('<select', $r[0]);
        } finally {
            DB::rollBack();
        }
    }

    public function test_blank_manual_row_is_removed_unconditionally()
    {
        DB::beginTransaction();
        try {
            $this->makeGroup('F1', ['40601101']);
            $src = file_get_contents(base_path('resources/views/transaction/multiplecashreceive.blade.php'));

            // The old guard made the removal impossible on a fresh page load,
            // where exactly one blank row exists.
            $fn = preg_match('/function removeBlankManualRows\(\)\s*\{(.*?)\n    \}/s', $src, $m) ? $m[1] : '';
            $this->assertNotEmpty($fn, 'removeBlankManualRows not found');
            $this->assertStringNotContainsString(
                'length > 1',
                $fn,
                'removeBlankManualRows must not keep a lone blank row alive'
            );
            $this->assertStringContainsString('isBlankManualRow', $fn);
        } finally {
            DB::rollBack();
        }
    }

    public function test_manual_dropdowns_follow_the_group_and_exhaustion_disables_add()
    {
        DB::beginTransaction();
        try {
            $this->makeGroup('F1', ['40601101']);

            $html = $this->actingAs(User::first())
                ->get('/Transaction/multipleCashReceive')->getContent();

            // The form carries the group id for the server-side check.
            $this->assertStringContainsString('name="collection_group_id"', $html);
            $this->assertStringContainsString('id="collectionGroupId"', $html);

            // New rule markers: manual dropdowns are rebuilt from the group,
            // locked codes are excluded, and the add button is gated.
            foreach ([
                'manualAllowedCodes',
                'rebuildManualOptions',
                'manualRowsNeedingClear',
                'updateAddButton',
                'cgAddNote',
                'fullOptions',
            ] as $marker) {
                $this->assertStringContainsString($marker, $html, 'missing JS behaviour: ' . $marker);
            }

            // Declined confirms can roll the dropdown back to the applied group.
            $this->assertStringContainsString('previousGroup', $html);
            $this->assertStringContainsString('setGroupControls', $html);
        } finally {
            DB::rollBack();
        }
    }

    public function test_crafted_post_with_outside_group_account_is_rejected()
    {
        DB::beginTransaction();
        try {
            $user = User::first();
            $group = $this->makeGroup('F1', ['40601101']);

            $before = DB::table('acct_tbl_unauth_transaction')->count();

            $response = $this->actingAs($user)->post('/Transaction/multipleCashReceive', [
                'PostingDate'         => date('d-m-Y'),
                'Serial'              => 'GRP-HACK',
                'collection_group_id' => $group->id,
                'rows' => [
                    ['CreditAccount' => '40601101', 'Amount' => 100, 'Particular' => 'inside group'],
                    ['CreditAccount' => '50101103', 'Amount' => 200, 'Particular' => 'outside group'],
                ],
            ]);

            $response->assertRedirect('/Transaction/multipleCashReceive');
            $response->assertSessionHas('error');
            $this->assertStringContainsString(
                'not part of the selected collection group',
                session('error')
            );
            $this->assertStringContainsString('50101103', session('error'));

            // Nothing was written.
            $this->assertSame(
                $before,
                DB::table('acct_tbl_unauth_transaction')->count(),
                'a rejected group post must write nothing'
            );
            $this->assertNull(session('lastReceipt'));
        } finally {
            DB::rollBack();
        }
    }

    public function test_post_with_stale_group_id_is_rejected()
    {
        DB::beginTransaction();
        try {
            $user = User::first();

            $response = $this->actingAs($user)->post('/Transaction/multipleCashReceive', [
                'PostingDate'         => date('d-m-Y'),
                'Serial'              => '',
                'collection_group_id' => 999999,
                'rows' => [
                    ['CreditAccount' => '40601101', 'Amount' => 100, 'Particular' => 'x'],
                ],
            ]);

            $response->assertRedirect('/Transaction/multipleCashReceive');
            $response->assertSessionHas('error');
            $this->assertStringContainsString('no longer available', session('error'));
        } finally {
            DB::rollBack();
        }
    }

    public function test_post_without_group_id_keeps_old_manual_behaviour()
    {
        DB::beginTransaction();
        try {
            $user = User::first();
            $this->makeGroup('F1', ['40601101']);

            // No collection_group_id posted: any valid head still saves.
            $response = $this->actingAs($user)->post('/Transaction/multipleCashReceive', [
                'PostingDate' => date('d-m-Y'),
                'Serial'      => 'GRP-NOGROUP',
                'rows' => [
                    ['CreditAccount' => '50101103', 'Amount' => 150, 'Particular' => 'manual head'],
                ],
            ]);

            $response->assertRedirect('/Transaction/multipleCashReceive');
            $receipt = session('lastReceipt');
            $this->assertSame(1, $receipt['count']);

            DB::table('acct_tbl_unauth_transaction')->whereIn('RefNumber', $receipt['refs'])->delete();
            DB::table('acct_tbl_transaction_tmp')->whereIn('RefNumber', $receipt['refs'])->delete();
        } finally {
            DB::rollBack();
        }
    }

    public function test_validation_redisplay_relocks_group_rows()
    {
        DB::beginTransaction();
        try {
            $user = User::first();
            $group = $this->makeGroup('F1', ['40601101']);

            // Invalid date fails validation; old input (rows + group id) is
            // flashed back and the next GET must re-lock the group row.
            // (followingRedirects cannot be used: without a referer the
            // validator falls back to '/' instead of the form page.)
            $this->actingAs($user)->post('/Transaction/multipleCashReceive', [
                'PostingDate'         => 'not-a-date',
                'Serial'              => '',
                'collection_group_id' => $group->id,
                'rows' => [
                    ['CreditAccount' => '40601101', 'Amount' => 100, 'Particular' => 'x'],
                ],
            ])->assertRedirect();

            $html = $this->actingAs($user)
                ->get('/Transaction/multipleCashReceive')
                ->assertStatus(200)
                ->getContent();

            $this->assertStringContainsString('restoreGroupRows', $html);
            $this->assertStringContainsString('id="collectionGroupId"', $html);
        } finally {
            DB::rollBack();
        }
    }

    public function test_no_group_selected_keeps_full_list_manual_behaviour()
    {
        DB::beginTransaction();
        try {
            $this->makeGroup('F1', ['40601101']);

            $html = $this->actingAs(User::first())
                ->get('/Transaction/multipleCashReceive')->getContent();

            // The initial row is still a full-list Select2 when no group is picked.
            preg_match('/<div id="receiveRows">(.*?)<\/div>\s*\n\s*@error/s', $html, $m);
            $this->assertStringContainsString('<select name="rows[0][CreditAccount]"', $html);
            $this->assertStringContainsString('+ Add another account', $html);
            $this->assertStringContainsString('rowTemplate', $html);
        } finally {
            DB::rollBack();
        }
    }

    public function test_saving_one_non_zero_group_row_uses_shared_receipt_and_shows_in_unauthorized_list()
    {
        DB::beginTransaction();
        try {
            $user = User::first();
            $group = $this->makeGroup('F1', ['40601101']);

            $response = $this->actingAs($user)->post('/Transaction/multipleCashReceive', [
                'PostingDate'     => date('d-m-Y'),
                'Serial'          => 'GRP-ONE',
                'CollectionGroup' => $group->id,
                'rows' => [
                    ['CreditAccount' => '40601101', 'Amount' => 777.25, 'Particular' => 'Only line'],
                ],
            ]);

            $response->assertRedirect('/Transaction/multipleCashReceive');
            $receipt = session('lastReceipt');

            $this->assertSame(1, $receipt['count']);
            $this->assertSame(777.25, (float) $receipt['total']);
            $this->assertSame('GRP-ONE', $receipt['receipt_no']);
            $this->assertCount(1, $receipt['refs']);

            $line = DB::table('acct_tbl_unauth_transaction')
                ->where('RefNumber', $receipt['refs'][0])->first();

            $this->assertNotNull($line);
            $this->assertSame('GRP-ONE', $line->Serial);
            $this->assertSame((string) config('accounting.cash_account'), $line->DrAccount);
            $this->assertSame('40601101', $line->CrAccount);
            $this->assertEquals(777.25, (float) $line->amount);

            // Visible in the Unauthorized List.
            $list = $this->actingAs($user)->get('/Transaction/UnauthorizeList?search=GRP-ONE');
            $list->assertStatus(200);
            $list->assertSee('GRP-ONE');

            DB::table('acct_tbl_unauth_transaction')->whereIn('RefNumber', $receipt['refs'])->delete();
            DB::table('acct_tbl_transaction_tmp')->whereIn('RefNumber', $receipt['refs'])->delete();
        } finally {
            DB::rollBack();
        }
    }

    public function test_zero_amount_rows_are_skipped_server_side()
    {
        DB::beginTransaction();
        try {
            $user = User::first();
            $group = $this->makeGroup('F3', ['40601101', '50101103', '50102101']);

            $response = $this->actingAs($user)->post('/Transaction/multipleCashReceive', [
                'PostingDate'     => date('d-m-Y'),
                'Serial'          => 'GRP-SKIP',
                'CollectionGroup' => $group->id,
                'rows' => [
                    ['CreditAccount' => '40601101', 'Amount' => 100, 'Particular' => 'a'],
                    ['CreditAccount' => '50101103', 'Amount' => 0,   'Particular' => ''],
                    ['CreditAccount' => '50102101', 'Amount' => 0,   'Particular' => ''],
                ],
            ]);

            $response->assertRedirect('/Transaction/multipleCashReceive');
            $receipt = session('lastReceipt');

            $this->assertSame(1, $receipt['count'], 'only the non-zero row may post');
            $this->assertSame(100.0, (float) $receipt['total']);

            DB::table('acct_tbl_unauth_transaction')->whereIn('RefNumber', $receipt['refs'])->delete();
            DB::table('acct_tbl_transaction_tmp')->whereIn('RefNumber', $receipt['refs'])->delete();
        } finally {
            DB::rollBack();
        }
    }
}