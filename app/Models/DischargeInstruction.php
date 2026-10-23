<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DischargeInstruction extends Model
{
    protected $table = 'discharge_instructions';

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
