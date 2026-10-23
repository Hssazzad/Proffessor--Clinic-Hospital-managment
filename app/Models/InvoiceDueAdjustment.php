<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDueAdjustment extends Model
{
    protected $table = 'invoice_due_adjustments';
    protected $guarded = [];

    protected $casts = [
        'adjustment_amount' => 'float',
        'reversed_at'       => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(InvestigationPayment::class, 'invoice_id', 'ID');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reverser()
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    public function reversal()
    {
        return $this->belongsTo(InvoiceDueAdjustment::class, 'reversal_of_id');
    }
}
