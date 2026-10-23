<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProcedureType extends Model
{
    use HasFactory;

    protected $table = 'procedure_types';

    protected $fillable = [
        'procedure_name',
        'stitch_followup',
        'stitch_fee',
        'expiry_days',
        'status',
    ];

    protected $casts = [
        'stitch_followup' => 'boolean',
        'stitch_fee'      => 'decimal:2',
        'expiry_days'     => 'integer',
        'status'          => 'boolean',
    ];

    /**
     * Admissions using this procedure type.
     */
    public function admissions()
    {
        return $this->hasMany(Admission::class, 'procedure_type_id');
    }
}