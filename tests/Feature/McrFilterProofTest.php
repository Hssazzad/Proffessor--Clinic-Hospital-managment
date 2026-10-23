<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\CollectionGroup;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * STEP 3 proof on the real page, printed for the record.
 */
class McrFilterProofTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.database', 'profclinic');
    }

    private function makeGroup(string $code, array $accountCodes): CollectionGroup
    {
        $g = CollectionGroup::where('code', $code)->first()
            ?? CollectionGroup::create(['code' => $code, 'name' => 'Group ' . $code, 'is_active' => 1]);
        $g->update(['is_active' => 1]);
        $g->accounts()->delete();
        foreach (array_values($accountCodes) as $i => $c) {
            $g->accounts()->create(['account_code' => $c, 'sort_order' => $i + 1]);
        }
        return $g;
    }

    public function test_proof()
    {
        DB::beginTransaction();
        try {
            $user = User::first();
            $html = $this->actingAs($user)->get('/Transaction/multipleCashReceive')
                ->assertStatus(200)->getContent();

            $payload = [];

            preg_match('/<template id="groupRowTemplate">(?:(?!<\/template>).)*<\/template>/s', $html, $g);
            preg_match('/<template id="rowTemplate">(?:(?!<\/template>).)*<\/template>/s', $html, $m);

            $out = [];
            $out[] = 'DB_HOST=' . config('database.connections.mysql.host')
                . '  DB_DATABASE=' . config('database.connections.mysql.database');
            $out[] = 'ROUTE=/Transaction/multipleCashReceive';
            $out[] = '';
            $out[] = 'GROUP ROW TEMPLATE contains <select>? ' . (strpos($g[0], '<select') !== false ? 'YES (BAD)' : 'NO (correct)');
            $out[] = 'GROUP ROW TEMPLATE contains hidden CreditAccount? '
                . (strpos($g[0], 'type="hidden" name="rows[__INDEX__][CreditAccount]"') !== false ? 'YES (correct)' : 'NO');
            $out[] = 'MANUAL ROW TEMPLATE contains <select>? ' . (strpos($m[0], '<select') !== false ? 'YES (full list)' : 'NO');
            $out[] = '';

            $all = DB::table('acct_tbl_balsheet_account')
                ->where('AccountNo', '!=', config('accounting.cash_account'))
                ->pluck('AccountNo')->map('strval')->all();
            $out[] = 'full chart of accounts on this page: ' . count($all) . ' heads';

            // Create the groups FIRST, then re-fetch the page so the payload
            // contains them (the controller only emits active groups).
            foreach ([1, 3] as $n) {
                $codes = array_slice(['40601101', '50101103', '50102101', '51501101', '10401101', '51901101'], 0, $n);
                $this->makeGroup('PROOF' . $n, $codes);
            }

            $html = $this->actingAs($user)->get('/Transaction/multipleCashReceive')
                ->assertStatus(200)->getContent();
            $start = strpos($html, 'const groupAccounts = ') + strlen('const groupAccounts = ');
            $payload = json_decode(substr($html, $start, strpos($html, ';', $start) - $start), true);

            foreach ([1, 3] as $n) {
                $g = CollectionGroup::where('code', 'PROOF' . $n)->first();
                $acc = $payload[(string) $g->id];
                $out[] = '';
                $out[] = "group with {$n} account(s) -> exposes " . count($acc) . ' account(s)';
                foreach ($acc as $a) {
                    $out[] = '   ' . $a['code'] . '  ' . $a['label'];
                }
                $this->assertCount($n, $acc);
            }

            $out[] = '';
            $out[] = '=== save 1 non-zero row from the 3-account group ===';
            $g3 = CollectionGroup::where('code', 'PROOF3')->first();
            $r = $this->actingAs($user)->post('/Transaction/multipleCashReceive', [
                'PostingDate' => date('d-m-Y'),
                'Serial'      => 'PROOF-RCPT',
                'CollectionGroup' => $g3->id,
                'rows' => [
                    ['CreditAccount' => '40601101', 'Amount' => 640, 'Particular' => 'proof line'],
                    ['CreditAccount' => '50101103', 'Amount' => 0, 'Particular' => ''],
                    ['CreditAccount' => '50102101', 'Amount' => 0, 'Particular' => ''],
                ],
            ]);
            $receipt = session('lastReceipt');
            $out[] = 'count=' . $receipt['count'] . '  total=' . $receipt['total']
                . '  receipt=' . $receipt['receipt_no'] . '  refs=' . implode(',', $receipt['refs']);

            $line = DB::table('acct_tbl_unauth_transaction')->where('RefNumber', $receipt['refs'][0])->first();
            $out[] = 'unauth row: Dr=' . $line->DrAccount . ' Cr=' . $line->CrAccount
                . ' amount=' . $line->amount . ' Serial=' . $line->Serial;

            $listHtml = $this->actingAs($user)->get('/Transaction/UnauthorizeList?search=PROOF-RCPT')->getContent();
            $out[] = 'appears in Unauthorized List? ' . (strpos($listHtml, 'PROOF-RCPT') !== false ? 'YES' : 'NO');

            $this->assertSame(1, $receipt['count']);
            $this->assertStringContainsString('PROOF-RCPT', $listHtml);

            DB::table('acct_tbl_unauth_transaction')->whereIn('RefNumber', $receipt['refs'])->delete();
            DB::table('acct_tbl_transaction_tmp')->whereIn('RefNumber', $receipt['refs'])->delete();

            fwrite(STDERR, "\n" . implode("\n", $out) . "\n");
            $this->assertTrue(true);
        } finally {
            DB::rollBack();
        }
    }
}