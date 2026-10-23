<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anesthesiologist extends Model
{
    protected $table = 'anesthesiologists';

    protected $fillable = [
        'name',
        'active',
    ];

    protected $attributes = [
        'active' => 1,
    ];

    public $timestamps = true;
}
