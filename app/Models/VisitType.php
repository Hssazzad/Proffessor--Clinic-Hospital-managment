<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitType extends Model
{
    protected $table = 'visit_types';

    protected $fillable = [
        'visit_type_name',
        'status'
    ];
}