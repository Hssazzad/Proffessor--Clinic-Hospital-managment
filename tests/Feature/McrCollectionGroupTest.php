<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\CollectionGroup;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * The Collection Group control must live in the card-header bar of the real
 * Multiple Cash Receive page, which is what the sidebar menu opens.
 */
class McrCollectionGroupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.database', 'profclinic');
    }

    private function seedGroup(string $code, string $name, array $accountCodes, bool $active = true): CollectionGroup
    {
        $group = CollectionGroup::create(['code' => $code, 'name' => $name, 'is_active' => $active ? 1 : 0]);
        $group->accounts()->delete();
        foreach (array_values($accountCodes) as $i => $accountCode) {
            $group->accounts()->create(['account_code' => $accountCode, 'sort_order' => $i + 1]);
        }
        return $group;
    }

    public function test_group_dropdown_renders_in_the_card_header_bar()
    {
        DB::beginTransaction();
        try {
            $this->seedGroup('MCG-1', 'Header Group', ['40601101', '50101103']);

            $html = $this->actingAs(User::first())
                ->get('/Transaction/multipleCashReceive')
                ->assertStatus(200)
                ->getContent();

            // The dropdown must be inside the card-header, i.e. BEFORE the form.
            $headerPos = strpos($html, 'class="card-header">');
            $selectPos = strpos($html, 'id="collectionGroup"');
            $formPos   = strpos($html, 'name="CollectionGroup"');

            $this->assertNotFalse($selectPos, 'collectionGroup select missing');
            $this->assertGreaterThan($headerPos, $selectPos, 'select is after the card-header');
            $this->assertLessThan(strpos($html, '<form method="POST"'), $selectPos,
                'select must sit inside the card-header, before the form');

            // Centred bar + exact label + exact placeholder + option format.
            $this->assertStringContainsString('cg-group-bar d-flex align-items-center justify-content-center', $html);
            $this->assertStringContainsString('Collection Group', $html);
            $this->assertStringContainsString('data-placeholder="-- Select Collection Group --"', $html);
            $this->assertStringContainsString('-- Select Collection Group --</option>', $html);
            $this->assertStringContainsString('[MCG-1] Header Group', $html);

            // Exactly one dropdown on the page.
            $this->assertSame(1, substr_count($html, 'id="collectionGroup"'));

            // The old inline position is gone.
            $this->assertStringNotContainsString('Select a group to fill rows automatically', $html);
        } finally {
            DB::rollBack();
        }
    }

    public function test_only_active_groups_are_offered()
    {
        DB::beginTransaction();
        try {
            $this->seedGroup('MCG-ACT', 'Active Group', ['40601101']);
            $this->seedGroup('MCG-OFF', 'Inactive Group', ['50101103'], false);

            $html = $this->actingAs(User::first())
                ->get('/Transaction/multipleCashReceive')
                ->getContent();

            $this->assertStringContainsString('[MCG-ACT] Active Group', $html);
            $this->assertStringNotContainsString('MCG-OFF', $html);
            $this->assertStringNotContainsString('Inactive Group', $html);
        } finally {
            DB::rollBack();
        }
    }

    public function test_group_rows_keep_the_manually_addable_row_and_locked_accounts()
    {
        DB::beginTransaction();
        try {
            $this->seedGroup('MCG-2', 'Row Group', ['40601101', '50101103']);

            $html = $this->actingAs(User::first())
                ->get('/Transaction/multipleCashReceive')
                ->getContent();

            // The group template must post the same field names the action validates.
            $this->assertStringContainsString('name="rows[__INDEX__][CreditAccount]" value="__ACCOUNT__"', $html);
            $this->assertStringContainsString('name="rows[__INDEX__][Amount]"', $html);
            $this->assertStringContainsString('name="rows[__INDEX__][Particular]"', $html);

            // Locked account cell, not a removable select.
            $this->assertStringContainsString('cg-locked-account', $html);
            $this->assertStringContainsString('cg-row-lock', $html);
            $this->assertStringNotContainsString('group-row"><div class="col-md-5"><select', $html);

            // Amount defaults to 0.00 so the row is skipped unless typed into.
            $this->assertMatchesRegularExpression('/groupRowTemplate.*?placeholder="Amount" value="0\.00"/s', $html);

            // Manual rows still have the remove (x) button and add-row still works.
            $this->assertStringContainsString('+ Add another account', $html);
            $this->assertStringContainsString('remove-row', $html);

            // The clear button guards typed amounts.
            $this->assertStringContainsString('hasTypedAmounts', $html);
            $this->assertStringContainsString('Amounts you typed will be lost', $html);

            // Blank manual rows are dropped once a group is chosen.
            $this->assertStringContainsString('removeBlankManualRows', $html);
        } finally {
            DB::rollBack();
        }
    }

    public function test_json_payload_carries_every_group_account_in_sort_order()
    {
        DB::beginTransaction();
        try {
            $group = $this->seedGroup('MCG-3', 'Payload Group', ['51501101', '40601101', '50101103']);

            $html = $this->actingAs(User::first())
                ->get('/Transaction/multipleCashReceive')
                ->getContent();

            $pos = strpos($html, 'const groupAccounts = ');
            $this->assertNotFalse($pos, 'groupAccounts payload missing');
            $start = $pos + strlen('const groupAccounts = ');
            $end = strpos($html, ';', $start);
            $json = substr($html, $start, $end - $start);
            $decoded = json_decode($json, true);

            $this->assertIsArray($decoded);
            $this->assertArrayHasKey((string) $group->id, $decoded);

            $codes = array_column($decoded[(string) $group->id], 'code');
            $this->assertSame(
                $group->accounts()->orderBy('sort_order')->pluck('account_code')->all(),
                $codes,
                'payload must follow sort_order'
            );

            foreach ($decoded[(string) $group->id] as $entry) {
                $this->assertNotEmpty($entry['label'], 'label must resolve for ' . $entry['code']);
            }
        } finally {
            DB::rollBack();
        }
    }

    public function test_saving_group_rows_writes_only_non_zero_amounts()
    {
        DB::beginTransaction();
        try {
            $user = User::first();
            $group = $this->seedGroup('MCG-4', 'Save Group', ['40601101', '50101103', '50102101']);

            $response = $this->actingAs($user)->post('/Transaction/multipleCashReceive', [
                'PostingDate'     => date('d-m-Y'),
                'Serial'          => 'MCGTEST',
                'CollectionGroup' => $group->id,
                'rows' => [
                    ['CreditAccount' => '40601101', 'Amount' => 1200.50, 'Particular' => 'Group line 1'],
                    ['CreditAccount' => '50101103', 'Amount' => 0,      'Particular' => ''],
                    ['CreditAccount' => '50102101', 'Amount' => 300,    'Particular' => 'Group line 3'],
                ],
            ]);

            $response->assertRedirect('/Transaction/multipleCashReceive');
            $response->assertSessionHas('lastReceipt');

            $receipt = session('lastReceipt');
            $this->assertSame(2, $receipt['count']);
            $this->assertSame(1500.5, (float) $receipt['total']);
            $this->assertSame('MCGTEST', $receipt['receipt_no']);

            $lines = DB::table('acct_tbl_unauth_transaction')
                ->whereIn('RefNumber', $receipt['refs'])
                ->where('ScreenType', 'MCR')
                ->get();

            $this->assertCount(2, $lines);
            $this->assertEqualsCanonicalizing(
                ['40601101', '50102101'],
                $lines->pluck('CrAccount')->all()
            );

            // Double-entry pair per line in the tmp ledger.
            $this->assertCount(4, DB::table('acct_tbl_transaction_tmp')
                ->whereIn('RefNumber', $receipt['refs'])->get());

            // Cleanup: this table is not transaction-safe.
            DB::table('acct_tbl_unauth_transaction')->whereIn('RefNumber', $receipt['refs'])->delete();
            DB::table('acct_tbl_transaction_tmp')->whereIn('RefNumber', $receipt['refs'])->delete();
        } finally {
            DB::rollBack();
        }
    }

    public function test_group_is_hidden_when_no_active_groups_exist()
    {
        DB::beginTransaction();
        try {
            CollectionGroup::query()->update(['is_active' => 0]);

            $html = $this->actingAs(User::first())
                ->get('/Transaction/multipleCashReceive')
                ->getContent();

            $this->assertStringContainsString('Save All Transactions', $html);
            $this->assertStringNotContainsString('id="collectionGroup"', $html);
        } finally {
            DB::rollBack();
        }
    }
}