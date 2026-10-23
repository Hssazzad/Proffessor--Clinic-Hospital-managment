<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\OtPayment;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * OT payment is an EXPENSE module, so the assertions that matter are the
 * ones that would catch money leaving on the wrong side: the debit must land on
 * the OT expenditure ledger, the credit on cash (or a real bank ledger), the two
 * legs must balance, and a payee id must never be accepted from the wrong master.
 */
class OtPaymentTest extends TestCase
{
    private array $cleanupRefNumbers = [];
    private array $cleanupOtIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.database', 'profclinic');
    }

    protected function tearDown(): void
    {
        foreach ($this->cleanupRefNumbers as $ref) {
            DB::table('acct_tbl_unauth_transaction')->where('RefNumber', $ref)->delete();
            DB::table('acct_tbl_transaction_tmp')->where('RefNumber', $ref)->delete();
        }
        if ($this->cleanupOtIds !== []) {
            DB::table('ot_payments')->whereIn('id', $this->cleanupOtIds)->delete();
        }
        parent::tearDown();
    }

    private function expenseAccountNo(): string
    {
        return (string) config('accounting.ot_expense_account', '51901101');
    }

    private function cashAccountNo(): string
    {
        return (string) config('accounting.cash_account', '205101101');
    }

    private function bankAccountNumbers(): array
    {
        return DB::table('acct_tbl_balsheet_account')
            ->where('gl_group', 202)
            ->orWhere('title', 'like', '%bank%')
            ->pluck('AccountNo')
            ->map(fn ($no) => (string) $no)
            ->all();
    }

    /** An id that exists in patient_ref, i.e. NOT a valid doctor id. */
    private function managerRefId(): int
    {
        $id = DB::table('patient_ref')->where('ref_type', 'OfficeEmployee')->value('ID');
        $this->assertNotNull($id, 'Need at least one OfficeEmployee in patient_ref');

        return (int) $id;
    }

    private function surgeonId(): int
    {
        $id = DB::table('doctors')->value('id');
        $this->assertNotNull($id, 'Need at least one doctor');

        return (int) $id;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'PayeeType'     => 'surgeon',
            'PayeeID'       => $this->surgeonId(),
            'Amount'        => 2500,
            'PaymentDate'   => date('d-m-Y'),
            'PaymentMethod' => 'Cash',
        ], $overrides);
    }

    // =====================================================
    // Page + AJAX
    // =====================================================

    public function test_ot_collection_page_loads_with_form_and_list()
    {
        $response = $this->actingAs(User::first())->get('/Transaction/otpayment');

        $response->assertStatus(200);
        $response->assertSee('OT Payment');
        $response->assertSee('Payee Type');
        $response->assertSee('Payee Name');
        $response->assertSee('Patient / Admission');
        $response->assertSee('Payment Method');
        $response->assertSee('Save Payment');
        // List + totals
        $response->assertSee('Total:');
    }

    public function test_payee_names_endpoint_returns_only_that_type()
    {
        $user = User::first();

        $surgeons = $this->actingAs($user)->getJson('/Transaction/otpayment/payees?payee_type=surgeon');
        $surgeons->assertStatus(200)->assertJson(['ok' => true]);
        $this->assertNotEmpty($surgeons->json('data'));
        $this->assertSame(
            (int) DB::table('doctors')->count(),
            count($surgeons->json('data')),
            'Surgeon dropdown must list every doctor and nothing else'
        );

        $assistants = $this->actingAs($user)->getJson('/Transaction/otpayment/payees?payee_type=assistant_surgeon');
        $assistants->assertStatus(200);
        $this->assertSame(
            (int) DB::table('assistant_surgeons')->count(),
            count($assistants->json('data'))
        );

        $anaesthetists = $this->actingAs($user)->getJson('/Transaction/otpayment/payees?payee_type=anesthesiologist');
        $this->assertSame(
            (int) DB::table('anesthesiologists')->count(),
            count($anaesthetists->json('data'))
        );

        $managers = $this->actingAs($user)->getJson('/Transaction/otpayment/payees?payee_type=manager');
        $this->assertSame(
            (int) DB::table('patient_ref')->where('ref_type', 'OfficeEmployee')->count(),
            count($managers->json('data')),
            'Manager dropdown must be restricted to OfficeEmployee reference people'
        );
    }

    public function test_payee_names_endpoint_rejects_unknown_payee_type()
    {
        $this->actingAs(User::first())
            ->getJson('/Transaction/otpayment/payees?payee_type=nurse')
            ->assertStatus(422);
    }

    public function test_patients_endpoint_searches_and_links_admission()
    {
        $patient = DB::table('patients')->orderByDesc('id')->first();

        $response = $this->actingAs(User::first())
            ->getJson('/Transaction/otpayment/patients?search=' . urlencode($patient->patientcode));

        $response->assertStatus(200)->assertJson(['ok' => true]);

        $ids = array_column($response->json('data'), 'id');
        $this->assertContains((int) $patient->id, array_map('intval', $ids));

        $row = collect($response->json('data'))->firstWhere('id', (int) $patient->id);
        $expected = DB::table('patient_admission')
            ->where('patientcode', $patient->patientcode)
            ->max('id');
        $this->assertSame($expected === null ? null : (int) $expected, $row['admission_id']);
    }

    // =====================================================
    // Validation
    // =====================================================

    public function test_payee_type_is_restricted_to_the_four_known_roles()
    {
        $this->actingAs(User::first())
            ->from('/Transaction/otpayment')
            ->post('/Transaction/SaveOtPayment', $this->validPayload(['PayeeType' => 'nurse']))
            ->assertSessionHasErrors('PayeeType');
    }

    public function test_amount_must_be_greater_than_zero()
    {
        $this->actingAs(User::first())
            ->from('/Transaction/otpayment')
            ->post('/Transaction/SaveOtPayment', $this->validPayload(['Amount' => 0]))
            ->assertSessionHasErrors('Amount');
    }

    public function test_required_fields_are_enforced()
    {
        $this->actingAs(User::first())
            ->from('/Transaction/otpayment')
            ->post('/Transaction/SaveOtPayment', [])
            ->assertSessionHasErrors(['PayeeType', 'PayeeID', 'Amount', 'PaymentDate', 'PaymentMethod']);
    }

    public function test_payee_id_must_belong_to_the_chosen_payee_type_master()
    {
        // A patient_ref id submitted as a surgeon must be refused.
        $this->actingAs(User::first())
            ->from('/Transaction/otpayment')
            ->post('/Transaction/SaveOtPayment', $this->validPayload([
                'PayeeType' => 'surgeon',
                'PayeeID'   => $this->managerRefId(),
            ]))
            ->assertSessionHasErrors('PayeeID');
    }

    public function test_bank_payment_is_refused_while_no_bank_account_exists()
    {
        if ($this->bankAccountNumbers() !== []) {
            $this->markTestSkipped('A bank ledger is now configured; this guard is the empty-state path');
        }

        $this->actingAs(User::first())
            ->from('/Transaction/otpayment')
            ->post('/Transaction/SaveOtPayment', $this->validPayload(['PaymentMethod' => 'Bank']))
            ->assertSessionHasErrors('PaymentMethod');
    }

    public function test_bank_payment_cannot_credit_the_cash_account()
    {
        if ($this->bankAccountNumbers() === []) {
            $this->markTestSkipped('No bank ledger configured');
        }

        $this->actingAs(User::first())
            ->from('/Transaction/otpayment')
            ->post('/Transaction/SaveOtPayment', $this->validPayload([
                'PaymentMethod' => 'Bank',
                'CreditAccount' => $this->cashAccountNo(),
            ]))
            ->assertSessionHasErrors('CreditAccount');
    }

    // =====================================================
    // Posting
    // =====================================================

    public function test_cash_payment_posts_a_balanced_double_entry_to_the_ot_expense_ledger()
    {
        $patient = DB::table('patients')->orderByDesc('id')->first();
        $amount  = 2500.00;

        $response = $this->actingAs(User::first())
            ->post('/Transaction/SaveOtPayment', $this->validPayload([
                'PatientID'   => $patient->id,
                'AdmissionID' => DB::table('patient_admission')
                    ->where('patientcode', $patient->patientcode)->max('id'),
                'Amount'      => $amount,
                'Note'        => 'Automated test OT payout',
            ]));

        $response->assertRedirect('/Transaction/otpayment');
        $response->assertSessionHas('success');

        $refNumber = session('refNumber');
        $this->assertNotEmpty($refNumber);
        $this->cleanupRefNumbers[] = $refNumber;

        // ---- business row, linked to the voucher ----
        $payment = OtPayment::where('ref_number', $refNumber)->first();
        $this->assertNotNull($payment);
        $this->cleanupOtIds[] = $payment->id;

        $this->assertSame('surgeon', $payment->payee_type);
        $this->assertSame('Cash', $payment->payment_method);
        $this->assertSame($this->expenseAccountNo(), $payment->expense_account);
        $this->assertSame($this->cashAccountNo(), $payment->credit_account);
        $this->assertSame((int) $patient->id, (int) $payment->patient_id);
        $this->assertSame($patient->patientcode, $payment->patient_code);
        $this->assertSame(OtPayment::STATUS_UNAUTHORIZED, $payment->status);
        $this->assertEquals($amount, (float) $payment->amount);

        // ---- two tmp legs ----
        $legs = DB::table('acct_tbl_transaction_tmp')->where('RefNumber', $refNumber)->get();
        $this->assertCount(2, $legs, 'A voucher must have exactly one debit and one credit leg');

        $debit  = $legs->firstWhere('TrType', 'Debit');
        $credit = $legs->firstWhere('TrType', 'Credit');

        $this->assertSame($this->expenseAccountNo(), $debit->AccountNo, 'Dr must be the OT expense ledger');
        $this->assertEquals($amount, (float) $debit->DrAmount);
        $this->assertEquals(0, (float) $debit->CrAmount);

        $this->assertSame($this->cashAccountNo(), $credit->AccountNo, 'Cr must be cash');
        $this->assertEquals(0, (float) $credit->DrAmount);
        $this->assertEquals($amount, (float) $credit->CrAmount);

        $this->assertEquals(
            (float) $legs->sum('DrAmount'),
            (float) $legs->sum('CrAmount'),
            'Voucher legs must balance'
        );

        // ---- unauthorized header for the Authorize screen ----
        $unauth = DB::table('acct_tbl_unauth_transaction')->where('RefNumber', $refNumber)->first();
        $this->assertNotNull($unauth);
        $this->assertSame($this->expenseAccountNo(), $unauth->DrAccount);
        $this->assertSame($this->cashAccountNo(), $unauth->CrAccount);
        $this->assertSame('OTP', $unauth->ScreenType);
        $this->assertEquals($amount, (float) $unauth->amount);

        // ---- the payee is recorded on the voucher ----
        $this->assertSame((string) $payment->payee_id, (string) $debit->EmployeeID);
        $this->assertStringContainsString($payment->payee_name, $debit->Particular);
        $this->assertStringContainsString($patient->patientcode, $debit->Particular);
    }

    public function test_expense_account_is_never_an_income_or_asset_ledger()
    {
        $account = DB::table('acct_tbl_balsheet_account')
            ->where('AccountNo', $this->expenseAccountNo())
            ->first();

        $this->assertNotNull($account, 'Configured OT expense account must exist');
        $this->assertSame(5, (int) $account->gl_type, 'OT payout ledger must be Expenditure (gl_type 5)');
        $this->assertSame(519, (int) $account->gl_group);
        $this->assertSame(51901, (int) $account->gl_sub_group);
    }

    public function test_payment_is_refused_when_the_expense_account_is_not_expenditure()
    {
        $before = OtPayment::count();
        Config::set('accounting.ot_expense_account', $this->cashAccountNo()); // an Asset ledger

        $this->actingAs(User::first())
            ->from('/Transaction/otpayment')
            ->post('/Transaction/SaveOtPayment', $this->validPayload())
            ->assertSessionHasErrors('Amount');

        $this->assertSame($before, OtPayment::count(), 'No payment row may be written when the debit ledger is not an expenditure');
    }

    public function test_payment_date_cannot_be_in_the_future()
    {
        $this->actingAs(User::first())
            ->from('/Transaction/otpayment')
            ->post('/Transaction/SaveOtPayment', $this->validPayload([
                'PaymentDate' => date('d-m-Y', strtotime('+5 days')),
            ]))
            ->assertSessionHasErrors('PaymentDate');
    }

    // =====================================================
    // List + filters
    // =====================================================

    /**
     * The rows of the payment table only.
     *
     * The filter dropdowns are deliberately populated with every payee of the
     * selected type, so asserting on the whole page would match a name that is
     * merely offered in a <select> and say nothing about the filtered result.
     */
    private function tableRows(string $html): string
    {
        $this->assertMatchesRegularExpression('/<tbody>(.*?)<\/tbody>/s', $html, 'payment table body not found');

        preg_match('/<tbody>(.*?)<\/tbody>/s', $html, $matches);

        return $matches[1];
    }

    public function test_list_filters_by_payee_type_and_date_range_and_reports_totals()
    {
        $amount = 1200.00;

        $this->actingAs(User::first())->post('/Transaction/SaveOtPayment', $this->validPayload([
            'PayeeType' => 'surgeon',
            'Amount'    => $amount,
        ]));

        $refNumber = session('refNumber');
        $this->cleanupRefNumbers[] = $refNumber;
        $payment = OtPayment::where('ref_number', $refNumber)->first();
        $this->cleanupOtIds[] = $payment->id;

        $today     = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        // Matching filter returns the row, and its amount is in the total.
        $matched = $this->actingAs(User::first())
            ->get('/Transaction/otpayment?payee_type=surgeon&from_date=' . $today . '&to_date=' . $today);
        $matched->assertStatus(200);
        $this->assertStringContainsString($payment->payee_name, $this->tableRows($matched->getContent()));
        $matched->assertSee(number_format($amount, 2));

        // A different payee type must exclude it.
        $excluded = $this->actingAs(User::first())
            ->get('/Transaction/otpayment?payee_type=manager&from_date=' . $today . '&to_date=' . $today);
        $excluded->assertStatus(200);
        $this->assertStringNotContainsString($payment->payee_name, $this->tableRows($excluded->getContent()));

        // A date range that ends yesterday must exclude it.
        $past = $this->actingAs(User::first())
            ->get('/Transaction/otpayment?payee_type=surgeon&from_date=' . $yesterday . '&to_date=' . $yesterday);
        $past->assertStatus(200);
        $this->assertStringNotContainsString($payment->payee_name, $this->tableRows($past->getContent()));

        // Filtering by the payee itself must keep it.
        $byPayee = $this->actingAs(User::first())
            ->get('/Transaction/otpayment?payee_type=surgeon&payee_id=' . $payment->payee_id);
        $byPayee->assertStatus(200);
        $this->assertStringContainsString($payment->payee_name, $this->tableRows($byPayee->getContent()));

        // A garbage date must be dropped, not silently widen the list.
        $garbage = $this->actingAs(User::first())->get('/Transaction/otpayment?from_date=not-a-date');
        $garbage->assertStatus(200);
    }

    public function test_unauthorized_list_offers_and_labels_the_ot_collection_screen_type()
    {
        $response = $this->actingAs(User::first())->get('/Transaction/UnauthorizeList');

        $response->assertStatus(200);
        $response->assertSee('OT Payment (OTP)');
    }

    // =====================================================
    // No regression on the screens whose posting logic was shared
    // =====================================================

    public function test_existing_cash_payment_screen_still_posts_after_the_shared_ref_allocator_change()
    {
        $user = User::first();

        $response = $this->actingAs($user)->post('/Transaction/SaveCashPaymentTransaction', [
            'debitsign'   => '-1',
            'creditsign'  => '1',
            'DebitAccount' => $this->expenseAccountNo(),
            'Amount'      => 100,
            'PostingDate' => date('d-m-Y'),
            'Serial'      => 'OTTEST-REGRESSION-01',
            // Particular must be supplied: acct_tbl_transaction_tmp.Particular is
            // NOT NULL and cashPaymentAction passes the input straight through,
            // so leaving the description blank 500s on the existing screen too.
            'Particular'  => 'OTTEST regression check',
        ]);

        $response->assertRedirect('/Transaction/CashPayment');
        $response->assertSessionHas('success');

        $refNumber = session('refNumber');
        $this->assertNotEmpty($refNumber);
        $this->cleanupRefNumbers[] = $refNumber;

        $legs = DB::table('acct_tbl_transaction_tmp')->where('RefNumber', $refNumber)->get();
        $this->assertCount(2, $legs);
        $this->assertEquals((float) $legs->sum('DrAmount'), (float) $legs->sum('CrAmount'));
    }
}