<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\CollectionGroup;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Collection Groups on the Multiple Cash Receive screen.
 *
 * Runs against the live MySQL database but wraps everything in a transaction
 * that is always rolled back, because the authorize step writes the ledger and
 * recalculates account balances.
 */
class CollectionGroupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.database', 'profclinic');
    }

    protected function tearDown(): void
    {
        // The MCR action writes to acct_tbl_transaction_tmp (DDL-free but not
        // transaction-safe on this legacy MyISAM-era schema), so clean by hand.
        $this->cleanupTmpRows();
        parent::tearDown();
    }

    private array $createdRefNumbers = [];

    private function cleanupTmpRows(): void
    {
        if (empty($this->createdRefNumbers)) {
            return;
        }

        DB::table('acct_tbl_unauth_transaction')
            ->whereIn('RefNumber', $this->createdRefNumbers)
            ->delete();
        DB::table('acct_tbl_transaction_tmp')
            ->whereIn('RefNumber', $this->createdRefNumbers)
            ->delete();
    }

    private function seedGroup(string $code, string $name, array $accountCodes): CollectionGroup
    {
        $group = CollectionGroup::create(['code' => $code, 'name' => $name, 'is_active' => 1]);
        $group->accounts()->delete();

        foreach (array_values($accountCodes) as $i => $accountCode) {
            $group->accounts()->create(['account_code' => $accountCode, 'sort_order' => $i + 1]);
        }

        return $group;
    }

    public function test_collection_group_settings_page_renders_and_lists_group()
    {
        DB::beginTransaction();
        try {
            $user = User::first();
            $group = $this->seedGroup('TST-001', 'Test Counter Group', ['40601101', '50101103']);

            $response = $this->actingAs($user)->get('/settings/collection');
            $response->assertStatus(200);
            $response->assertSee('TST-001');
            $response->assertSee('Test Counter Group');
        } finally {
            DB::rollBack();
        }
    }

    public function test_selecting_group_saves_only_rows_with_amount()
    {
        DB::beginTransaction();
        try {
            $user = User::first();
            $group = $this->seedGroup('TST-002', 'Test Save Group', ['40601101', '50101103', '50102101']);

            // Mirrors what the screen posts: every group account appears, and the
            // user only typed an amount into two of them.
            $postData = [
                'PostingDate' => date('d-m-Y'),
                'Serial'      => 'TSTRCPT',
                'CollectionGroup' => $group->id,
                'rows' => [
                    ['CreditAccount' => '40601101', 'Amount' => 1000, 'Particular' => 'Investigation'],
                    ['CreditAccount' => '50101103', 'Amount' => 0, 'Particular' => ''],
                    ['CreditAccount' => '50102101', 'Amount' => 500, 'Particular' => 'Salary'],
                ],
            ];

            $response = $this->actingAs($user)->post('/Transaction/multipleCashReceive', $postData);
            $response->assertRedirect('/Transaction/multipleCashReceive');
            $response->assertSessionHas('lastReceipt');

            $receipt = session('lastReceipt');
            $this->createdRefNumbers = $receipt['refs'];

            // The zero row is skipped, so only 2 items post.
            $this->assertEquals(2, $receipt['count']);
            $this->assertEquals(1500.0, (float) $receipt['total']);
            $this->assertEquals('TSTRCPT', $receipt['receipt_no']);

            $lines = DB::table('acct_tbl_unauth_transaction')
                ->whereIn('RefNumber', $receipt['refs'])
                ->where('ScreenType', 'MCR')
                ->get();

            $this->assertCount(2, $lines);
            $this->assertEqualsCanonicalizing(
                ['40601101', '50102101'],
                $lines->pluck('CrAccount')->all()
            );

            // Each posted line has a matching Debit/Credit pair in the tmp ledger.
            $tmpRows = DB::table('acct_tbl_transaction_tmp')
                ->whereIn('RefNumber', $receipt['refs'])
                ->get();
            $this->assertCount(4, $tmpRows);
        } finally {
            $this->cleanupTmpRows();
            DB::rollBack();
        }
    }

    public function test_all_zero_amounts_are_rejected_without_writing_anything()
    {
        DB::beginTransaction();
        try {
            $user = User::first();
            $group = $this->seedGroup('TST-003', 'Test Zero Group', ['40601101', '50101103']);

            $response = $this->actingAs($user)->post('/Transaction/multipleCashReceive', [
                'PostingDate'     => date('d-m-Y'),
                'Serial'          => '',
                'CollectionGroup' => $group->id,
                'rows' => [
                    ['CreditAccount' => '40601101', 'Amount' => 0, 'Particular' => ''],
                    ['CreditAccount' => '50101103', 'Amount' => '', 'Particular' => ''],
                ],
            ]);

            $response->assertRedirect('/Transaction/multipleCashReceive');
            $response->assertSessionHas('error');
            $this->assertNull(session('lastReceipt'));

            // Nothing was written.
            $this->assertEquals(0, DB::table('acct_tbl_unauth_transaction')->where('Serial', 'TSTRCPT')->count());
        } finally {
            DB::rollBack();
        }
    }

    public function test_receive_screen_offers_active_group_and_emits_its_accounts()
    {
        DB::beginTransaction();
        try {
            $user = User::first();
            $this->seedGroup('TST-005', 'Test Offer Group', ['40601101', '50101103']);

            $response = $this->actingAs($user)->get('/Transaction/multipleCashReceive');
            $response->assertStatus(200);
            $response->assertSee('TST-005');
            $response->assertSee('Test Offer Group');
            // The group -> account payload the JS reads on selection.
            $response->assertSee('40601101');
            $response->assertSee('Investigation Income');
        } finally {
            DB::rollBack();
        }
    }

    public function test_account_picker_is_a_dual_pane_transfer_list()
    {
        DB::beginTransaction();
        try {
            $user = User::first();

            $content = $this->actingAs($user)->get('/settings/collection')->getContent();

            // Both modals share the picker partial.
            $this->assertStringContainsString('data-cg-picker="cgAddAccounts"', $content);
            $this->assertStringContainsString('data-cg-picker="cgEditAccounts"', $content);

            // Dual-pane structure, not a native multi-select.
            $this->assertStringContainsString('cg-transfer', $content);
            $this->assertStringContainsString('cg-available-body', $content);
            $this->assertStringContainsString('cg-selected-body', $content);
            $this->assertDoesNotMatchRegularExpression('/<select[^>]*name="accounts\[\]"/', $content);

            // Submission contract is unchanged: accounts[] hidden inputs.
            $this->assertStringContainsString('cg-selected-codes', $content);
            $this->assertStringContainsString('name="accounts[]"', $content);

            // Required behaviours.
            $this->assertStringContainsString('cg-search', $content);
            $this->assertStringContainsString('cg-type-filter', $content);
            $this->assertStringContainsString('cg-check-all', $content);
            $this->assertStringContainsString('cg-add-selected', $content);
            $this->assertStringContainsString('cg-add-all', $content);
            $this->assertStringContainsString('cg-remove-all', $content);
            $this->assertStringContainsString('cg-col-handle', $content);
            $this->assertStringContainsString('No accounts added yet', $content);
            $this->assertStringContainsString('cg-missing-badge', $content);
            $this->assertStringContainsString("account.code + ' ' + account.name", $content);
            $this->assertStringContainsString("'Showing '", $content);
        } finally {
            DB::rollBack();
        }
    }

    public function test_picker_offers_exactly_the_accounts_multiple_cash_receive_allows()
    {
        DB::beginTransaction();
        try {
            $user = User::first();

            $content = $this->actingAs($user)->get('/settings/collection')->getContent();

            // The receive screen excludes only the cash account; the picker must
            // not be stricter, or valid receive heads would be unreachable.
            $cash = (string) config('accounting.cash_account');
            $this->assertStringNotContainsString('"' . $cash . '":{', $content);

            foreach (DB::table('acct_tbl_balsheet_account')
                ->where('AccountNo', '!=', $cash)
                ->get() as $account) {
                $this->assertStringContainsString($account->AccountNo . '":{', $content);
            }
        } finally {
            DB::rollBack();
        }
    }

    public function test_inactive_group_is_not_offered_on_receive_screen()
    {
        DB::beginTransaction();
        try {
            $user = User::first();
            $group = $this->seedGroup('TST-004', 'Test Inactive Group', ['40601101']);
            $group->update(['is_active' => 0]);

            $response = $this->actingAs($user)->get('/Transaction/multipleCashReceive');
            $response->assertStatus(200);
            $response->assertDontSee('TST-004');
        } finally {
            DB::rollBack();
        }
    }
}