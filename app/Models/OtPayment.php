<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtPayment extends Model
{
    protected $table = 'ot_payments';

    protected $fillable = [
        'payee_type',
        'payee_id',
        'payee_name',
        'patient_id',
        'admission_id',
        'patient_code',
        'patient_name',
        'amount',
        'payment_date',
        'payment_method',
        'note',
        'expense_account',
        'credit_account',
        'ref_number',
        'status',
        'created_by',
    ];

    protected $casts = [
        'payee_id'     => 'integer',
        'patient_id'   => 'integer',
        'admission_id' => 'integer',
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
        'created_by'   => 'integer',
    ];

    /**
     * The only payee types this screen accepts.
     * Also the source of the `in:` validation rule on the payee type field.
     */
    public const PAYEE_TYPES = [
        'surgeon'           => 'Surgeon',
        'assistant_surgeon' => 'Assistant Surgeon',
        'anesthesiologist'   => 'Anesthesiologist',
        'manager'           => 'Manager',
    ];

    public const PAYMENT_METHODS = [
        'Cash' => 'Cash',
        'Bank' => 'Bank',
    ];

    /** Queued in acct_tbl_unauth_transaction, not yet in acct_tbl_transaction. */
    public const STATUS_UNAUTHORIZED = 'unauthorized';

    /** Authorized onto the ledger by TransactionController::authorizeTransaction(). */
    public const STATUS_POSTED = 'posted';

    /**
     * Where the "Payee Name" dropdown reads its options from for a payee type.
     *
     * There is no single payee table — each role has its own master, and the
     * PayeeID is only meaningful within its own table. Keeping the mapping here
     * means the AJAX endpoint and the store validation cannot disagree about
     * which master a payee_id belongs to.
     *
     * Managers are read from patient_ref where ref_type = 'OfficeEmployee'
     * (the staff reference list the payment screens already use for "Taken By"),
     * because this database has no managers master of its own.
     *
     * @return array{table: string, name: string, where?: array}|null
     */
    public static function payeeSource(?string $payeeType): ?array
    {
        return match ($payeeType) {
            'surgeon'           => ['table' => 'doctors',             'name' => 'doctor_name'],
            'assistant_surgeon' => ['table' => 'assistant_surgeons', 'name' => 'name'],
            'anesthesiologist'   => ['table' => 'anesthesiologists',  'name' => 'name'],
            'manager'           => ['table' => 'patient_ref',        'name' => 'Name',   'where' => ['ref_type' => 'OfficeEmployee']],
            default             => null,
        };
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function payeeTypeLabel(): string
    {
        return self::PAYEE_TYPES[$this->payee_type] ?? (string) $this->payee_type;
    }

    public function isPosted(): bool
    {
        return $this->status === self::STATUS_POSTED;
    }
}