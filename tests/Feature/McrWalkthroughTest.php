<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\CollectionGroup;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * End-to-end walk of the Multiple Cash Receive screen, driven through the real
 * HTTP layer so the rendered markup and the JS payload are what gets asserted.
 */
class McrWalkthroughTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.database', 'profclinic');
    }

    public function test_walkthrough()
    {
        DB::beginTransaction();
        try {
            // Realistic group matching the task's verification strings.
            foreach ([
                ['402', '40203', '40203101', 'Admission Fee'],
                ['402', '40204', '40204101', 'Consultancy Fee'],
                ['405', '40501', '40501101', 'OT Charge'],
            ] as [$g, $sg, $no, $title]) {
                if (!DB::table('acct_tbl_balsheet_account')->where('AccountNo', $no)->exists()) {
                    DB::table('acct_tbl_balsheet_account')->insert([
                        'gl_type' => 4, 'gl_group' => $g, 'gl_sub_group' => $sg,
                        'AccountNo' => $no, 'title' => $title,
                        'AccountType' => 0, 'Balance' => 0,
                    ]);
                }
            }

            $group = CollectionGroup::where('code', '001')->first()
                ?? CollectionGroup::create(['code' => '001', 'name' => 'OPD Counter Collection', 'is_active' => 1]);
            $group->update(['name' => 'OPD Counter Collection', 'is_active' => 1]);
            $group->accounts()->delete();
            // Deliberately saved in a non-alphabetical order to prove sort_order
            // is what drives the row order.
            foreach ([
                ['account_code' => '40204101', 'sort_order' => 1],
                ['account_code' => '40203101', 'sort_order' => 2],
                ['account_code' => '40501101', 'sort_order' => 3],
            ] as $row) {
                $group->accounts()->create($row);
            }

            $user = User::first();
            $html = $this->actingAs($user)->get('/Transaction/multipleCashReceive')
                ->assertStatus(200)->getContent();

            fwrite(STDERR, "\n=== 1. card-header bar ===\n");
            $bar = strpos($html, 'cg-group-bar');
            fwrite(STDERR, substr($html, $bar - 90, 780) . "\n");

            // JSON payload the JS uses on selection.
            $start = strpos($html, 'const groupAccounts = ') + strlen('const groupAccounts = ');
            $decoded = json_decode(substr($html, $start, strpos($html, ';', $start) - $start), true);
            fwrite(STDERR, "\n=== 2. JSON payload (group " . $group->id . ") ===\n");
            fwrite(STDERR, json_encode($decoded[(string) $group->id], JSON_PRETTY_PRINT) . "\n");

            $this->assertSame(
                ['40204101', '40203101', '40501101'],
                array_column($decoded[(string) $group->id], 'code'),
                'JS payload must follow sort_order, not alphabetical'
            );
            $this->assertStringContainsString('[40203101] Admission Fee',
                '[40203101] ' . $decoded[(string) $group->id][1]['label']);

            // 3. Verify the searching strings the task lists.
            fwrite(STDERR, "\n=== 3. search coverage (label contains code+name, so both match) ===\n");
            foreach ($decoded[(string) $group->id] as $a) {
                fwrite(STDERR, sprintf("  %-10s %s\n", $a['code'], $a['label']));
            }
            $this->assertStringContainsString('40203', $html);   // GL path searchable via data
            $this->assertStringContainsString('admission', strtolower($html));

            // 4. Save three rows, leave one at 0 -> only 2 post.
            $response = $this->actingAs($user)->post('/Transaction/multipleCashReceive', [
                'PostingDate'     => date('d-m-Y'),
                'Serial'          => 'WALK-001',
                'CollectionGroup' => $group->id,
                'rows' => [
                    ['CreditAccount' => '40204101', 'Amount' => 900.25, 'Particular' => 'Consultancy'],
                    ['CreditAccount' => '40203101', 'Amount' => 1500.00, 'Particular' => 'Admission'],
                    ['CreditAccount' => '40501101', 'Amount' => 0,      'Particular' => ''],
                ],
            ]);
            $response->assertRedirect('/Transaction/multipleCashReceive');

            $receipt = session('lastReceipt');
            fwrite(STDERR, "\n=== 4. save result ===\n");
            fwrite(STDERR, 'count=' . $receipt['count'] . ' total=' . $receipt['total']
                . ' receipt=' . $receipt['receipt_no'] . ' refs=' . implode(',', $receipt['refs']) . "\n");

            $this->assertSame(2, $receipt['count']);
            $this->assertSame(2400.25, (float) $receipt['total']);

            $lines = DB::table('acct_tbl_unauth_transaction')
                ->whereIn('RefNumber', $receipt['refs'])->where('ScreenType', 'MCR')->get();
            fwrite(STDERR, 'posted lines: ' . $lines->pluck('CrAccount')->implode(',') . "\n");
            $this->assertEqualsCanonicalizing(['40204101', '40203101'], $lines->pluck('CrAccount')->all());

            // 5. Authorization still works on a group receipt.
            $review = $this->actingAs($user)->get('/Transaction/AuthorizeReview?ref_number=' . $receipt['refs'][0]);
            $review->assertStatus(200);
            $this->assertStringContainsString('Itemwise Breakup', $review->getContent());
            fwrite(STDERR, "\n=== 5. authorize review renders ===\nOK\n");

            DB::table('acct_tbl_unauth_transaction')->whereIn('RefNumber', $receipt['refs'])->delete();
            DB::table('acct_tbl_transaction_tmp')->whereIn('RefNumber', $receipt['refs'])->delete();
        } finally {
            DB::rollBack();
        }
    }
}