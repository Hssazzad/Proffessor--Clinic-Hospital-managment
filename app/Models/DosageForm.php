<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DosageForm extends Model
{
    public $timestamps = false;

    protected $table = 'dosage_forms';

    protected $fillable = [
        'dosage_name',
        'active',
    ];
}
