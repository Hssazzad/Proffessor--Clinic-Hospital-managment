<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowupDue extends Model
{
    protected $table = 'followup_dues';

    protected $fillable = [
        'admission_id',
        'patientcode',
        'procedure_type_id',
        'admission_date',
        'due_deadline',
        'amount',
        'status',
        'paid_amount',
        'resolved_at',
        'resolved_by',
    ];

    public function getComputedStatusAttribute()
    {
        if ($this->status === 'paid') {
            return 'paid';
        }

        if ($this->status === 'pending' && $this->due_deadline < now()->toDateString()) {
            return 'waived';
        }

        return 'pending';
    }
}
