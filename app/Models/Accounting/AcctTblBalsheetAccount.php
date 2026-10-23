<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class AcctTblBalsheetAccount extends Model
{
    protected $table = 'acct_tbl_balsheet_account';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'gl_type',
        'gl_group',
        'gl_sub_group',
        'AccountNo',
        'title',
        'AccountType',
        'Balance',
    ];
}