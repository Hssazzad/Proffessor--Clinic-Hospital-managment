<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChamberFeeConfig extends Model
{
    protected $table = 'chamber_fee_configs';

    public $timestamps = false;

    protected $fillable = [
        'chamber_name',
        'first_visit_fee',
        'followup_fee',
        'expired_fee',
        'followup_days',
        'status'
    ];
}