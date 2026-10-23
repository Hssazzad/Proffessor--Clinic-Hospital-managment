<?php

namespace App\Http\Controllers;

use App\Models\Accounting\AcctTblBalsheetAccount;
use App\Models\OtPayment;
use App\Services\Accounting\GlVoucherQueue;
use App\Services\Accounting\GlType;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * OT Payment — the EXPENSE side of an operation.
 *
 * When an OT happens the clinic pays out to the operation team (surgeon,
 * assistant surgeon, anaesthetist, manager). This screen records those payouts
 * and posts each one as a balanced voucher:
 *
 *   Dr  config('accounting.ot_expense_account')   OT payout expense
 *   Cr  config('accounting.cash_account')          when Payment Method = Cash
 *   Cr  the selected Bank ledger                   when Payment Method = Bank
 *
 * This is deliberately NOT income — no receivable is created and nothing is
 * billed to the patient here. Billing of the OT fee to the patient stays in the
 * billing module; this screen only moves money OUT.
 *
 * Posting follows the exact path of the existing Cash / Bank Payment screens:
 * two legs into acct_tbl_transaction_tmp plus one acct_tbl_unauth_transaction
 * header, so the voucher waits on the Authorize screen like every other manual
 * voucher. The allocated RefNumber is written back onto the ot_payments row to
 * keep the payment and its ledger entry linked.
 */
class OtPaymentController extends Controller
{
    private const TBL_ACCOUNT = 'acct_tbl_balsheet_account';
    private const TBL_BALANCE = 'acct_tbl_balance';

    private const PAGE_SIZE = 25;
    private const PATIENT_SEARCH_LIMIT = 50;

    // =====================================================
    // Configuration helpers
    // =====================================================

    private function cashAccountNo(): string
    {
        return (string) config('accounting.cash_account', '205101101');
    }

    /**
     * The expenditure ledger debited by every OT payout.
     */
    private function expenseAccountNo(): string
    {
        return (string) config('accounting.ot_expense_account', '51901101');
    }

    private function screenType(): string
    {
        return (string) config('accounting.ot_screen_type', 'OTP');
    }

    /**
     * Bank ledgers a payout can be made from.
     *
     * Same detection as the Bank Payment screen (GL group 202, or "bank" in the
     * title) but WITHOUT that screen's fallback to "any 2xx asset that isn't
     * cash": with no real bank ledger configured, that fallback resolves to
     * Patient Accounts Receivable, and silently crediting a receivable for a
     * real cash payout out to a surgeon is unrecoverable. An empty result means
     * "Bank is not available yet", which the form surfaces instead.
     */
    private function bankAccounts(): Collection
    {
        return AcctTblBalsheetAccount::where('gl_group', 202)
            ->orWhere('title', 'like', '%bank%')
            ->orderBy('title')
            ->get(['AccountNo', 'title', 'gl_group', 'gl_sub_group']);
    }

    private function postingDateRules(): array
    {
        $min = (string) config('accounting.posting.min_date', '2000-01-01');
        $max = now()->addDays((int) config('accounting.posting.future_days', 0))->format('Y-m-d');

        return ['required', 'date', "after_or_equal:{$min}", "before_or_equal:{$max}"];
    }

    private function validationMessages(): array
    {
        return [
            'PayeeType.in'       => 'Payee type must be Surgeon, Assistant Surgeon, Anesthesiologist or Manager.',
            'PayeeID.required'   => 'Select the payee name for the chosen payee type.',
            'Amount.gt'          => 'Amount must be greater than zero.',
            'PaymentMethod.in'   => 'Payment method must be Cash or Bank.',
            'PaymentDate.after_or_equal'  => 'Payment date is earlier than the accounting system allows.',
            'PaymentDate.before_or_equal' => 'Payment date cannot be in the future.',
            'CreditAccount.exists' => 'The selected bank account does not exist.',
        ];
    }

    // =====================================================
    // OT Payment — form + filtered list
    // =====================================================
    public function index(Request $request)
    {
        $expenseAccountNo = $this->expenseAccountNo();
        $cashAccountNo    = $this->cashAccountNo();

        $expenseAccount = AcctTblBalsheetAccount::where('AccountNo', $expenseAccountNo)->first();
        $bankAccounts   = $this->bankAccounts();

        $cashBalance = DB::table(self::TBL_BALANCE)
            ->where('AccountNo', $cashAccountNo)
            ->value('Balance');

        // ---- filters ----
        $filters = [
            'payee_type' => (string) $request->input('payee_type', ''),
            'payee_id'   => $request->input('payee_id'),
            'patient_id' => $request->input('patient_id'),
            'from_date'  => (string) $request->input('from_date', ''),
            'to_date'    => (string) $request->input('to_date', ''),
        ];

        $filtered = $this->filteredPaymentsQuery($filters);

        $totalCount  = (clone $filtered)->count();
        $totalAmount = (float) (clone $filtered)->sum('amount');

        $payments = $filtered
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(self::PAGE_SIZE)
            ->withQueryString();

        // Payee options for the *filter* dropdown only — server-rendered so the
        // currently filtered payee survives a reload. The form's payee dropdown
        // is AJAX-driven (see payeeNames).
        $filterSource = OtPayment::payeeSource($filters['payee_type']);
        $filterPayees = $filterSource ? $this->payeeNameQuery($filters['payee_type'], '', 500) : collect();

        // Patients that actually have an OT payout, so the filter list stays short.
        $filterPatients = OtPayment::query()
            ->whereNotNull('patient_id')
            ->select('patient_id', 'patient_code', 'patient_name')
            ->distinct()
            ->orderBy('patient_name')
            ->get();

        // The patient whose submit bounced back on a validation error — the
        // Select2 is AJAX-populated, so without this the field empties itself.
        $oldPatient = null;
        $oldPatientId = (int) $request->session()->get('_old_input.PatientID', 0);
        if ($oldPatientId > 0) {
            $oldPatient = DB::table('patients')
                ->where('id', $oldPatientId)
                ->first(['id', 'patientcode', 'patientname']);
        }

        return view('transaction.otpayment', [
            'expenseAccount'    => $expenseAccount,
            'expenseAccountNo'  => $expenseAccountNo,
            'expenseAccountOk'  => $this->isUsableExpenseAccount($expenseAccount),
            'bankAccounts'      => $bankAccounts,
            'cashAccountNo'     => $cashAccountNo,
            'cashBalance'       => (float) ($cashBalance ?? 0),
            'refNumber'         => session('refNumber'),
            'payeeTypes'        => OtPayment::PAYEE_TYPES,
            'payments'          => $payments,
            'filters'           => $filters,
            'filterPayees'      => $filterPayees,
            'filterPatients'    => $filterPatients,
            'oldPatient'        => $oldPatient,
            'totalCount'        => $totalCount,
            'totalAmount'       => $totalAmount,
        ]);
    }

    // =====================================================
    // OT Payment — save
    // =====================================================
    public function store(Request $request)
    {
        // Built with the facade rather than $request->validate() because the
        // Bank-credit rule below has to be registered as an `after` hook, which
        // validate() has already completed by the time it returns.
        $validator = Validator::make($request->all(), [
            'PayeeType'     => ['required', 'string', 'in:' . implode(',', array_keys(OtPayment::PAYEE_TYPES))],
            'PayeeID'       => ['required', 'integer', 'min:1'],
            'PatientID'     => ['nullable', 'integer', 'min:1'],
            'AdmissionID'   => ['nullable', 'integer', 'min:1'],
            'Amount'        => ['required', 'numeric', 'gt:0'],
            'PaymentDate'   => $this->postingDateRules(),
            'PaymentMethod' => ['required', 'string', 'in:' . implode(',', array_keys(OtPayment::PAYMENT_METHODS))],
            'CreditAccount' => ['nullable', 'string', 'exists:' . self::TBL_ACCOUNT . ',AccountNo'],
            'Note'          => ['nullable', 'string', 'max:255'],
        ], $this->validationMessages());

        $validator->after(function ($validator) use ($request) {
            $this->validateBankCredit($validator, (string) $request->input('PaymentMethod'), $request->input('CreditAccount'));
        });

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        // ---- Payee must exist in ITS OWN master ----
        $payeeType = (string) $request->input('PayeeType');
        $payeeId   = (int) $request->input('PayeeID');
        $payeeName = $this->findPayeeName($payeeType, $payeeId);

        if ($payeeName === null) {
            return back()->withInput()->withErrors([
                'PayeeID' => 'The selected payee does not exist under this payee type. Pick the name again.',
            ]);
        }

        // ---- Debit side: the OT expense ledger ----
        $expenseAccountNo = $this->expenseAccountNo();
        $expenseAccount   = AcctTblBalsheetAccount::where('AccountNo', $expenseAccountNo)->first();

        if (!$this->isUsableExpenseAccount($expenseAccount)) {
            \Log::error('OT Payment: expense account not usable', ['account' => $expenseAccountNo]);

            return back()->withInput()->withErrors([
                'Amount' => "OT expense account {$expenseAccountNo} is missing from the chart of accounts, so this payment cannot be posted. Create it under Accounting -> Add Account/Ledger or set ACCT_OT_EXPENSE_ACCOUNT.",
            ]);
        }

        // ---- Credit side: cash, or the chosen bank ledger ----
        $paymentMethod = (string) $request->input('PaymentMethod');
        $creditAccount = $paymentMethod === 'Bank'
            ? (string) $request->input('CreditAccount')
            : $this->cashAccountNo();

        // ---- Patient / admission ----
        $patient   = $this->findPatient($request->input('PatientID'));
        $admission = $this->findAdmission($request->input('AdmissionID'), $patient);

        $amount      = round((float) $request->input('Amount'), 2);
        $postingDate = date('Y-m-d', strtotime((string) $request->input('PaymentDate')));
        $note        = trim((string) $request->input('Note', ''));

        $particular = $this->buildParticular($payeeType, $payeeName, $patient, $note);

        return DB::transaction(function () use (
            $payeeType, $payeeId, $payeeName, $patient, $admission,
            $amount, $postingDate, $paymentMethod, $note,
            $expenseAccountNo, $creditAccount, $particular
        ) {
            $refNumber = app(GlVoucherQueue::class)->queueVoucher(
                debitAccount:  $expenseAccountNo,
                creditAccount: $creditAccount,
                amount:        $amount,
                particular:    $particular,
                postingDate:   $postingDate,
                screenType:    $this->screenType(),
                extra:         ['EmployeeID' => $payeeId],
            );

            OtPayment::create([
                'payee_type'      => $payeeType,
                'payee_id'        => $payeeId,
                'payee_name'      => $payeeName,
                'patient_id'      => $patient->id ?? null,
                'admission_id'    => $admission->id ?? null,
                'patient_code'    => $patient->patientcode ?? null,
                'patient_name'    => $patient->patientname ?? null,
                'amount'          => $amount,
                'payment_date'    => $postingDate,
                'payment_method'  => $paymentMethod,
                'note'            => $note !== '' ? $note : null,
                'expense_account' => $expenseAccountNo,
                'credit_account'  => $creditAccount,
                'ref_number'      => (string) $refNumber,
                'status'          => OtPayment::STATUS_UNAUTHORIZED,
                'created_by'      => Auth::id(),
            ]);

            session(['refNumber' => $refNumber]);

            return redirect()
                ->route('transaction.otpayment')
                ->with('success', 'OT Payment saved successfully.');
        });
    }

    // =====================================================
    // AJAX — payee names for the selected payee type
    // =====================================================
    public function payeeNames(Request $request)
    {
        $validated = $request->validate([
            'payee_type' => ['required', 'string', 'in:' . implode(',', array_keys(OtPayment::PAYEE_TYPES))],
        ], $this->validationMessages());

        return response()->json([
            'ok'   => true,
            'data' => $this->payeeNameQuery($validated['payee_type'], (string) $request->input('search', '')),
        ]);
    }

    // =====================================================
    // AJAX — searchable patients for the payment form
    // =====================================================
    public function patients(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $rows = DB::table('patients as p')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('p.patientname', 'like', "%{$search}%")
                    ->orWhere('p.patientcode', 'like', "%{$search}%")
                    ->orWhere('p.mobile_no', 'like', "%{$search}%");
            }))
            ->orderByDesc('p.id')
            ->limit(self::PATIENT_SEARCH_LIMIT)
            ->get(['p.id', 'p.patientcode', 'p.patientname', 'p.mobile_no']);

        // patient_admission stores patients.patientcode, not patients.id, so the
        // join key is resolved here in PHP rather than through a second query.
        $latestAdmissions = DB::table('patient_admission')
            ->select('patientcode', DB::raw('MAX(id) as id'))
            ->groupBy('patientcode')
            ->pluck('id', 'patientcode');

        $admissionLabels = DB::table('patient_admission')
            ->whereIn('id', $latestAdmissions->values()->all())
            ->pluck('admissionid', 'id');

        $data = $rows->map(function ($patient) use ($latestAdmissions, $admissionLabels) {
            $admissionPk = $latestAdmissions[$patient->patientcode] ?? null;

            return [
                'id'           => (int) $patient->id,
                'patientcode'  => $patient->patientcode,
                'patientname'  => $patient->patientname,
                'mobile_no'    => $patient->mobile_no,
                'admission_id' => $admissionPk !== null ? (int) $admissionPk : null,
                'admissionid'  => $admissionPk !== null ? ($admissionLabels[$admissionPk] ?? null) : null,
            ];
        });

        return response()->json(['ok' => true, 'data' => $data]);
    }

    // =====================================================
    // Internals
    // =====================================================

    /**
     * Base query for the list, driven by the filter inputs.
     */
    private function filteredPaymentsQuery(array $filters)
    {
        $knownType = array_key_exists($filters['payee_type'], OtPayment::PAYEE_TYPES);
        $fromDate  = $this->toDateString($filters['from_date']);
        $toDate    = $this->toDateString($filters['to_date']);

        return OtPayment::query()
            ->when($knownType, fn ($query) => $query->where('payee_type', $filters['payee_type']))
            ->when($filters['payee_id'], fn ($query) => $query->where('payee_id', $filters['payee_id']))
            ->when($filters['patient_id'], fn ($query) => $query->where('patient_id', $filters['patient_id']))
            ->when($fromDate, fn ($query) => $query->where('payment_date', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->where('payment_date', '<=', $toDate));
    }

    /**
     * Filter dates arrive free-text from the query string, so an unparseable one
     * is dropped rather than silently becoming 1970-01-01 and emptying the list.
     */
    private function toDateString(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? null : date('Y-m-d', $timestamp);
    }

    /**
     * Active names of one payee type, as [{id, name}].
     */
    private function payeeNameQuery(string $payeeType, string $search = '', int $limit = 500): Collection
    {
        $source = OtPayment::payeeSource($payeeType);

        if ($source === null) {
            return collect();
        }

        return DB::table($source['table'])
            ->select('id', $source['name'] . ' as name')
            ->when(($source['where'] ?? null), fn ($query, $where) => $query->where($where))
            ->when($search !== '', fn ($query) => $query->where($source['name'], 'like', "%{$search}%"))
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => ['id' => (int) $row->id, 'name' => (string) $row->name]);
    }

    /**
     * Resolve a payee name inside the master that belongs to its payee type.
     *
     * Resolving server-side is what stops a crafted request from pairing
     * payee_type = 'surgeon' with an id that only exists in patient_ref.
     */
    private function findPayeeName(string $payeeType, int $payeeId): ?string
    {
        $source = OtPayment::payeeSource($payeeType);

        if ($source === null) {
            return null;
        }

        $name = DB::table($source['table'])
            ->where('id', $payeeId)
            ->when(($source['where'] ?? null), fn ($query, $where) => $query->where($where))
            ->value($source['name']);

        return $name === null ? null : (string) $name;
    }

    private function findPatient($patientId): ?object
    {
        if (empty($patientId)) {
            return null;
        }

        return DB::table('patients')->where('id', (int) $patientId)->first();
    }

    /**
     * The admission must belong to the selected patient, otherwise the payment
     * would point at an unrelated admission.
     */
    private function findAdmission($admissionId, $patient): ?object
    {
        if (empty($admissionId) || $patient === null) {
            return null;
        }

        return DB::table('patient_admission')
            ->where('id', (int) $admissionId)
            ->where('patientcode', $patient->patientcode)
            ->first();
    }

    /**
     * An expenditure ledger must exist and must actually be expenditure-natured,
     * otherwise the voucher would post a payout as income.
     */
    private function isUsableExpenseAccount(?AcctTblBalsheetAccount $account): bool
    {
        return $account !== null && (int) $account->gl_type === GlType::EXPENDITURE;
    }

    /**
     * Bank payouts may only credit a real bank ledger.
     */
    private function validateBankCredit($validator, string $paymentMethod, $creditAccount): void
    {
        if ($paymentMethod !== 'Bank') {
            return;
        }

        $bankNumbers = $this->bankAccounts()
            ->pluck('AccountNo')
            ->map(fn ($no) => (string) $no)
            ->all();

        if ($bankNumbers === []) {
            $validator->errors()->add('PaymentMethod', 'No bank account is configured under GL Group 202 (Bank). Add one in Accounting -> Add Account/Ledger, then pay by bank. Cash is still available.');
            return;
        }

        if (empty($creditAccount) || ! in_array((string) $creditAccount, $bankNumbers, true)) {
            $validator->errors()->add('CreditAccount', 'Select the bank account this OT payment is made from.');
        }
    }

    /**
     * Ledger narrative. The payee, the patient and the note all live here so the
     * voucher is readable on its own in the ledger and on the Authorize screen.
     */
    private function buildParticular(string $payeeType, string $payeeName, $patient, string $note): string
    {
        $typeLabel = OtPayment::PAYEE_TYPES[$payeeType] ?? $payeeType;

        $parts = ['OT Payment (' . $typeLabel . '): ' . $payeeName];

        if ($patient !== null) {
            $parts[] = 'Patient: ' . trim(($patient->patientcode ?? '') . ' - ' . ($patient->patientname ?? ''), ' -');
        }

        if ($note !== '') {
            $parts[] = $note;
        }

        return implode(' | ', $parts);
    }
}