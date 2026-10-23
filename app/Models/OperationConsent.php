<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationConsent extends Model
{
    protected $table = 'operation_consents';

    protected $fillable = [
        'admission_id',
        'patient_id',
        'weight',
        'ward_no',
        'post_office',
        'disease_name',
        'operation_name',
        'operation_type',
        'operation_date',
        'operation_time',
        'bed_no',
        'surgeon_name',
        'assistant_surgeon',
        'anesthesiologist',
        'operation_note',
        'signatory_name',
        'signatory_relation',
        'doctor_signature_name',
        'created_by',
    ];

    protected $casts = [
        'operation_date' => 'date',
    ];
}