<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class AcctTblBalsheetGroup extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'acct_tbl_balsheet_group';

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
        'gl_type',
        'tradingaccount',
        'gl_group',
        'gl_group_name',
        'Balance',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'gl_type'        => 'integer',
        'tradingaccount' => 'integer',
        'gl_group'       => 'integer',
        'Balance'        => 'decimal:2',
    ];
}