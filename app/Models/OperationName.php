<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationName extends Model
{
    protected $table = 'operation_names';

    protected $fillable = [
        'name',
        'active',
    ];

    protected $attributes = [
        'active' => 1,
    ];

    public $timestamps = true;
}
