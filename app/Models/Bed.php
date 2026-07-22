<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    protected $table = 'beds';

    protected $fillable = [
        'bed_number',
        'bed_type',
        'price_per_day',
        'status',
        'current_admission_id',
    ];

    /**
     * Fix: beds.current_admission_id → patient_admission.id
     * আগে ভুলভাবে 'admissionid' (varchar custom code) দিয়ে match করত,
     * কিন্তু storeadmitpatient() আসলে patient_admission.id (primary key) সেভ করে।
     */
    public function currentAdmission()
    {
        return $this->belongsTo(PatientAdmission::class, 'current_admission_id', 'id');
    }
}