<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DischargeDiagnosis extends Model
{
    protected $table = 'discharge_diagnoses';

    protected $fillable = [
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $attributes = [
        'active' => true,
    ];

    public $timestamps = true;
}
