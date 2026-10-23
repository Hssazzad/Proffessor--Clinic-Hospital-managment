<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreconassessmentValue extends Model
{
    use HasFactory;

    protected $table = 'preconassessment_values';

    protected $fillable = [
        'assessment_id',
        'oe_master_id',
        'value',
    ];

    public function assessment()
    {
        return $this->belongsTo(PreConAssessment::class, 'assessment_id');
    }

    public function oeMaster()
    {
        return $this->belongsTo(OeMaster::class, 'oe_master_id');
    }
}
