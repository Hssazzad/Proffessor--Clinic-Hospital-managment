<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurgeryPrescriptionDiagnosis extends Model
{
    protected $table = 'surgery_prescription_diagnoses';

    protected $fillable = [
        'surgery_prescription_id', 'name', 'note',
    ];
}
