<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurgeryPrescriptionMedicine extends Model
{
    protected $table = 'surgery_prescription_medicines';

    protected $fillable = [
        'prescription_id', 'medicine_name', 'name', 'strength', 'dose',
        'route', 'frequency', 'duration', 'timing', 'note',
    ];
}
