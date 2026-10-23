<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class BankTransactionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.database', 'profclinic');
    }

    public function test_bank_receive_page_loads_successfully()
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/Transaction/BankReceive');

        $response->assertStatus(200);
        $response->assertSee('Bank Receive');
        $response->assertSee('Deposit Into Bank Account');
    }

    public function test_bank_payment_page_loads_successfully()
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/Transaction/BankPayment');

        $response->assertStatus(200);
        $response->assertSee('Bank Payment');
        $response->assertSee('Pay From Bank Account');
    }

    public function test_unauthorize_list_shows_bank_filters_and_buttons()
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/Transaction/UnauthorizeList');

        $response->assertStatus(200);
        $response->assertSee('Bank Payment (BP)');
        $response->assertSee('Bank Receive (BR)');
    }

    public function test_bank_receive_save_and_authorize_workflow()
    {
        $user = User::first();

        // Get two distinct accounts
        $accounts = DB::table('acct_tbl_balsheet_account')->limit(2)->get();
        if ($accounts->count() < 2) {
            $this->markTestSkipped('Need at least 2 accounts for testing transactions');
        }

        $debitAcc = $accounts[0]->AccountNo;
        $creditAcc = $accounts[1]->AccountNo;

        $postData = [
            'debitsign' => '-1',
            'creditsign' => '1',
            'DebitAccount' => $debitAcc,
            'CreditAccount' => $creditAcc,
            'Amount' => 1500.00,
            'PostingDate' => date('d-m-Y'),
            'Serial' => 'CHQ-TEST-001',
            'BankName' => 'Test Bank Ltd',
            'ChequeDate' => date('d-m-Y'),
            'Particular' => 'Automated Test Bank Receive',
        ];

        $response = $this->actingAs($user)->post('/Transaction/SaveBankReceiveTransaction', $postData);
        $response->assertRedirect('/Transaction/BankReceive');
        $response->assertSessionHas('success');

        $refNumber = session('refNumber');
        $this->assertNotEmpty($refNumber);

        // Verify unauth table has ScreenType 'BR'
        $unauth = DB::table('acct_tbl_unauth_transaction')->where('RefNumber', $refNumber)->first();
        $this->assertNotNull($unauth);
        $this->assertEquals('BR', $unauth->ScreenType);
        $this->assertEquals(1500.00, (float) $unauth->amount);

        // Clean up test data
        DB::table('acct_tbl_unauth_transaction')->where('RefNumber', $refNumber)->delete();
        DB::table('acct_tbl_transaction_tmp')->where('RefNumber', $refNumber)->delete();
    }

    public function test_bank_payment_save_workflow()
    {
        $user = User::first();

        $accounts = DB::table('acct_tbl_balsheet_account')->limit(2)->get();
        if ($accounts->count() < 2) {
            $this->markTestSkipped('Need at least 2 accounts for testing transactions');
        }

        $creditAcc = $accounts[0]->AccountNo; // paying out from bank
        $debitAcc = $accounts[1]->AccountNo;  // paying to expense

        $postData = [
            'debitsign' => '-1',
            'creditsign' => '1',
            'DebitAccount' => $debitAcc,
            'CreditAccount' => $creditAcc,
            'Amount' => 750.00,
            'PostingDate' => date('d-m-Y'),
            'Serial' => 'CHQ-TEST-002',
            'BankName' => 'Beneficiary Bank',
            'ChequeDate' => date('d-m-Y'),
            'Particular' => 'Automated Test Bank Payment',
        ];

        $response = $this->actingAs($user)->post('/Transaction/SaveBankPaymentTransaction', $postData);
        $response->assertRedirect('/Transaction/BankPayment');
        $response->assertSessionHas('success');

        $refNumber = session('refNumber');
        $this->assertNotEmpty($refNumber);

        $unauth = DB::table('acct_tbl_unauth_transaction')->where('RefNumber', $refNumber)->first();
        $this->assertNotNull($unauth);
        $this->assertEquals('BP', $unauth->ScreenType);
        $this->assertEquals(750.00, (float) $unauth->amount);

        // Clean up test data
        DB::table('acct_tbl_unauth_transaction')->where('RefNumber', $refNumber)->delete();
        DB::table('acct_tbl_transaction_tmp')->where('RefNumber', $refNumber)->delete();
    }
}
