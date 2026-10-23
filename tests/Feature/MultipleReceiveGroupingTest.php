<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MultipleReceiveGroupingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.database', 'profclinic');
    }

    public function test_multiple_receive_without_serial_groups_as_one_receipt()
    {
        // Never touch real balances: authorize writes ledger + recalculates,
        // so the whole flow runs inside a transaction that is always rolled back.
        DB::beginTransaction();
        try {
            $this->runGroupingFlow();
        } finally {
            DB::rollBack();
        }
    }

    private function runGroupingFlow(): void
    {
        $user = User::first();
        $accounts = DB::table('acct_tbl_balsheet_account')->limit(3)->get();
        if ($accounts->count() < 3) {
            $this->markTestSkipped('Need at least 3 accounts for testing');
        }

        $postData = [
            'PostingDate' => date('d-m-Y'),
            'Serial' => '',
            'rows' => [
                ['CreditAccount' => $accounts[0]->AccountNo, 'Amount' => 100, 'Particular' => 'Item A'],
                ['CreditAccount' => $accounts[1]->AccountNo, 'Amount' => 200, 'Particular' => 'Item B'],
            ],
        ];

        $response = $this->actingAs($user)->post('/Transaction/multipleCashReceive', $postData);
        $response->assertRedirect('/Transaction/multipleCashReceive');
        $response->assertSessionHas('lastReceipt');

        $receipt = session('lastReceipt');
        $this->assertEquals(2, $receipt['count']);
        $this->assertEquals(300.0, (float) $receipt['total']);
        $this->assertCount(2, $receipt['refs']);

        // Both lines share one receipt number even though user typed nothing.
        $serials = DB::table('acct_tbl_unauth_transaction')
            ->whereIn('RefNumber', $receipt['refs'])
            ->pluck('Serial')
            ->unique();
        $this->assertCount(1, $serials);

        // Review page shows single payment total + itemwise breakup.
        $review = $this->actingAs($user)->get('/Transaction/AuthorizeReview?ref_number=' . $receipt['refs'][0]);
        $review->assertStatus(200);
        $review->assertSee('Itemwise Breakup');
        $review->assertSee(number_format(300, 2));

        // Authorizing one line authorizes the whole receipt as one payment.
        $auth = $this->actingAs($user)->post('/Transaction/authorize', [
            'ref_numbers' => $receipt['refs'][0],
        ]);
        $auth->assertRedirect('/Transaction/UnauthorizeList');
        $remaining = DB::table('acct_tbl_unauth_transaction')
            ->whereIn('RefNumber', $receipt['refs'])
            ->count();
        $this->assertEquals(0, $remaining);

        // Cleanup authorized ledger rows created by this test.
        DB::table('acct_tbl_transaction')->whereIn('RefNumber', $receipt['refs'])->delete();
    }
}
