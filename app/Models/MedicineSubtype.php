<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineSubtype extends Model
{
    protected $table = 'medicine_subtypes';

    protected $fillable = [
        'medicine_type_id',
        'subtype_name',
        'active',
    ];

    public function medicineType()
    {
        return $this->belongsTo(MedicineType::class);
    }
}
