<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestigationPaymentItem extends Model
{
    protected $table = 'investigation_payment_items';
    protected $guarded = [];

    protected $casts = [
        'UnitPrice' => 'decimal:2',
        'Amount'    => 'decimal:2',
    ];

    public function payment()
    {
        return $this->belongsTo(InvestigationPayment::class, 'PaymentId');
    }
}