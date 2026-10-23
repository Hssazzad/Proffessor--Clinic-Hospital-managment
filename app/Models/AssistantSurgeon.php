<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssistantSurgeon extends Model
{
    protected $table = 'assistant_surgeons';

    protected $fillable = [
        'name',
        'active',
    ];

    protected $attributes = [
        'active' => 1,
    ];

    public $timestamps = true;
}
