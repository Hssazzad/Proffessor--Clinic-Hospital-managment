<?php

namespace App\Models;

use App\Models\Accounting\AcctTblBalsheetAccount;
use Illuminate\Database\Eloquent\Model;

class CollectionGroupAccount extends Model
{
    protected $table = 'collection_group_accounts';

    protected $fillable = [
        'collection_group_id',
        'account_code',
        'sort_order',
    ];

    public function collectionGroup()
    {
        return $this->belongsTo(CollectionGroup::class, 'collection_group_id');
    }

    /**
     * Resolved against acct_tbl_balsheet_account by AccountNo, the key the
     * transaction screens submit. A renumbered or deleted account leaves this
     * null so the settings page can flag it instead of silently dropping the row.
     */
    public function account()
    {
        return $this->belongsTo(AcctTblBalsheetAccount::class, 'account_code', 'AccountNo');
    }
}