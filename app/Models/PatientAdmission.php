<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientAdmission extends Model
{
    protected $table = 'patient_admission';

    protected $guarded = [];

    public $timestamps = true;

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patientcode', 'patientcode');
    }
}
