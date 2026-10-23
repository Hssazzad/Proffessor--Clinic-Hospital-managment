<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionGroup extends Model
{
    protected $table = 'collection_groups';

    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function accounts()
    {
        return $this->hasMany(CollectionGroupAccount::class, 'collection_group_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}