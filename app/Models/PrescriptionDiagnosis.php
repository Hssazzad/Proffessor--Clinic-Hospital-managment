<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionDiagnosis extends Model
{
    protected $table = 'prescriptions_diagnosis';

    protected $fillable = [
        'patientcode', 'prescription_id', 'name', 'note', 'active', 'name_normalized',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}
