<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OeMaster extends Model
{
    protected $table = 'oe_master';

    protected $fillable = [
        'name',
        'short_name',
        'unit',
        'input_type',
        'sort_order',
        'active',
        'field_code',
    ];

    protected $casts = [
        'active'     => 'boolean',
        'sort_order' => 'integer',
        'field_code' => 'integer',
    ];
}
