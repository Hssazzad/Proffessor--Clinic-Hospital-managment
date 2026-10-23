<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class AcctGltypeInfo extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'acct_gltype_info';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'ID';

    /**
     * Indicates if the model should be timestamped.
     * (Table has no created_at / updated_at columns.)
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'code' => 'integer',
    ];
}