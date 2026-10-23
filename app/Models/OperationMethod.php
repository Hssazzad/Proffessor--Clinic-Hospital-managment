<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationMethod extends Model
{
    protected $table = 'operation_methods';

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
