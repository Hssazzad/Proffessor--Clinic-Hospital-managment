<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestigationPrice extends Model
{
    protected $table    = 'investigations';
    protected $fillable = ['name', 'category', 'price', 'status'];
    protected $casts    = ['price' => 'float'];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}