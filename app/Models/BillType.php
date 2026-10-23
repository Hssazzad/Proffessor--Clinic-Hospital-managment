<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillType extends Model
{
    use HasFactory;

    protected $table = 'bill_types';

    
    const UPDATED_AT = null; 

    protected $fillable = [
        'slug',
        'name',
        'icon',
        'color',
        'main_code',
        'requires_doctor',
        'requires_category',
        'free_text_items',
        'is_active',
        'sort_order',
    ];

        protected $casts = [
        'requires_doctor'   => 'boolean',
        'requires_category' => 'boolean',
        'free_text_items'   => 'boolean',
        'is_active'         => 'boolean',
    ];
}