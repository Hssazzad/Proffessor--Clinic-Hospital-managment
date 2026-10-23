<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for the `prescription_prints` table.
 *
 * Stores an immutable snapshot of every prescription print for audit trail,
 * reprint, and legal compliance purposes.
 */
class PrescriptionPrint extends Model
{
    use HasFactory;

    /** @var string The table associated with the model. */
    protected $table = 'prescription_prints';

    /** @var array The attributes that are mass assignable. */
    protected $fillable = [
        'patientcode',
        'admissionid',
        'order_type',
        'stage',
        'medicines_snapshot',
        'patient_snapshot',
        'doctor_snapshot',
        'clinical_snapshot',
        'print_hash',
        'printed_by',
        'printed_at',
        'ip_address',
    ];

    /** @var array The attributes that should be cast to native types. */
    protected $casts = [
        'medicines_snapshot' => 'array',
        'patient_snapshot'   => 'array',
        'doctor_snapshot'    => 'array',
        'clinical_snapshot'  => 'array',
        'printed_at'         => 'datetime',
        'stage'              => 'integer',
    ];

    /**
     * The user who printed this prescription.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by');
    }

    /**
     * Scope a query to only include prints for a given patient code.
     *
     * @param  Builder<$this>  $query
     * @param  string  $patientcode
     * @return Builder<$this>
     */
    public function scopeForPatient(Builder $query, string $patientcode): Builder
    {
        return $query->where('patientcode', $patientcode);
    }

    /**
     * Scope a query to only include prints for a given admission id.
     *
     * @param  Builder<$this>  $query
     * @param  string  $admissionid
     * @return Builder<$this>
     */
    public function scopeForAdmission(Builder $query, string $admissionid): Builder
    {
        return $query->where('admissionid', $admissionid);
    }

    /**
     * Scope a query to only include prints of a given order type.
     *
     * @param  Builder<$this>  $query
     * @param  string  $orderType
     * @return Builder<$this>
     */
    public function scopeOfType(Builder $query, string $orderType): Builder
    {
        return $query->where('order_type', $orderType);
    }
}