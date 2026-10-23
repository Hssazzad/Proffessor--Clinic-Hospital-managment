<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Accounting\AcctTblBalsheetAccount;
use Illuminate\Support\Facades\Config;

class BankAccountExclusionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.database', 'profclinic');
    }

    /** Bank-side accounts exactly as the controller's bankAccountNumbers() resolves them. */
    private function bankAccountNumbers(): array
    {
        $numbers = AcctTblBalsheetAccount::where('gl_group', 202)
            ->orWhere('title', 'like', '%bank%')
            ->pluck('AccountNo')->map(fn ($n) => (string) $n)->all();

        if ($numbers === []) {
            $numbers = AcctTblBalsheetAccount::where('gl_group', 'like', '2%')
                ->where('AccountNo', '!=', config('accounting.cash_account', '205101101'))
                ->pluck('AccountNo')->map(fn ($n) => (string) $n)->all();
        }

        return $numbers;
    }

    /** Extract the <select name="X">...</select> block from rendered HTML. */
    private function selectBlock(string $html, string $name): string
    {
        $pattern = '/<select[^>]+name="' . preg_quote($name, '/') . '".*?<\/select>/s';
        preg_match($pattern, $html, $matches);
        $this->assertNotEmpty($matches, "select[name={$name}] not found in page");

        return $matches[0];
    }

    public function test_bank_receive_page_excludes_bank_accounts_from_receive_from_dropdown()
    {
        $user = User::first();
        $bankNumbers = $this->bankAccountNumbers();
        if ($bankNumbers === []) {
            $this->markTestSkipped('No bank-side accounts configured to exclude');
        }

        $response = $this->actingAs($user)->get('/Transaction/BankReceive');
        $response->assertStatus(200);

        $html = $response->getContent();

        // "Receive From Account" (CreditAccount) must not list any bank account.
        $creditBlock = $this->selectBlock($html, 'CreditAccount');
        foreach ($bankNumbers as $accountNo) {
            $this->assertStringNotContainsString('value="' . $accountNo . '"', $creditBlock,
                "Bank account {$accountNo} must not appear in Receive From dropdown");
        }

        // "Deposit Into Bank Account" (DebitAccount) must still list them.
        $debitBlock = $this->selectBlock($html, 'DebitAccount');
        foreach ($bankNumbers as $accountNo) {
            $this->assertStringContainsString('value="' . $accountNo . '"', $debitBlock,
                "Bank account {$accountNo} should appear in Deposit Into dropdown");
        }
    }

    public function test_bank_payment_page_excludes_bank_accounts_from_payment_to_dropdown()
    {
        $user = User::first();
        $bankNumbers = $this->bankAccountNumbers();
        if ($bankNumbers === []) {
            $this->markTestSkipped('No bank-side accounts configured to exclude');
        }

        $response = $this->actingAs($user)->get('/Transaction/BankPayment');
        $response->assertStatus(200);

        $html = $response->getContent();

        // "Payment To Account" (DebitAccount) must not list any bank account.
        $debitBlock = $this->selectBlock($html, 'DebitAccount');
        foreach ($bankNumbers as $accountNo) {
            $this->assertStringNotContainsString('value="' . $accountNo . '"', $debitBlock,
                "Bank account {$accountNo} must not appear in Payment To dropdown");
        }

        // "Pay From Bank Account" (CreditAccount) must still list them.
        $creditBlock = $this->selectBlock($html, 'CreditAccount');
        foreach ($bankNumbers as $accountNo) {
            $this->assertStringContainsString('value="' . $accountNo . '"', $creditBlock,
                "Bank account {$accountNo} should appear in Pay From dropdown");
        }
    }

    public function test_bank_receive_save_rejects_bank_account_as_source()
    {
        $user = User::first();
        $bankNumbers = $this->bankAccountNumbers();
        if ($bankNumbers === []) {
            $this->markTestSkipped('No bank-side accounts configured to exclude');
        }

        $other = AcctTblBalsheetAccount::whereNotIn('AccountNo', $bankNumbers)->first();

        $response = $this->actingAs($user)->post('/Transaction/SaveBankReceiveTransaction', [
            'debitsign' => -1,
            'creditsign' => 1,
            'DebitAccount' => $other->AccountNo,
            'CreditAccount' => $bankNumbers[0],
            'Amount' => 10,
            'PostingDate' => date('d-m-Y'),
        ]);

        $response->assertSessionHasErrors('CreditAccount');
    }
}
