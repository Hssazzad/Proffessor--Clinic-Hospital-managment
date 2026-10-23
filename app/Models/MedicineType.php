<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineType extends Model
{
    protected $table = 'medicine_types';

    public $timestamps = false;

    protected $fillable = [
        'type_name',
        'active',
    ];

    public function subTypes()
    {
        return $this->hasMany(MedicineSubtype::class, 'medicine_type_id');
    }

    public function activeSubTypes()
    {
        return $this->hasMany(MedicineSubtype::class, 'medicine_type_id')->where('active', 1);
    }
}
