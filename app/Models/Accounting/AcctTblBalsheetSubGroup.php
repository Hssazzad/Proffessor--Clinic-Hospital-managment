<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class AcctTblBalsheetSubGroup extends Model
{
    protected $table = 'acct_tbl_balsheet_sub_group';

    protected $primaryKey = 'ID';

    // No created_at/updated_at columns — only Last_update_time
    public $timestamps = false;

    protected $fillable = [
        'gl_type',
        'tradingaccount',
        'gl_group',
        'gl_sub_group',
        'gl_sub_group_name',
        'Balance',
    ];

    protected $casts = [
        'gl_type'       => 'integer',
        'tradingaccount' => 'integer',
        'gl_group'      => 'integer',
        'gl_sub_group'  => 'integer',
        'Balance'       => 'float',
        'Last_update_time' => 'datetime',
    ];
}