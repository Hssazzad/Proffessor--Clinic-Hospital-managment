<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    protected $table = 'beds';

    protected $fillable = [
        'bed_name',
        'bed_number',
        'bed_type',
        'price_per_day',
        'status',
        'current_admission_id',
    ];

   
    public function currentAdmission()
    {
        return $this->belongsTo(PatientAdmission::class, 'current_admission_id', 'id');
    }
}