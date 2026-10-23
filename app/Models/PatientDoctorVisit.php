<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientDoctorVisit extends Model
{
    protected $table = 'patient_doctor_visits';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'invoice_id',
        'chamber_fee_config_id',
        'visit_type_id',
        'consultation_fee',
        'visit_date'
    ];
}