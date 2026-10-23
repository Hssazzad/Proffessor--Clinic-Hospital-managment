<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestigationPayment extends Model
{
    protected $table = 'investigation_payments';
    protected $guarded = [];

    protected $casts = [
        'PaymentDate'         => 'date',
        'fin_last_payment_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(InvestigationPaymentItem::class, 'PaymentId');
    }

    public function scopeFilterByPatient($query, $patientId)
    {
        return $query->when($patientId, fn ($q) => $q->where('PatientId', $patientId));
    }

    public function scopeFilterByService($query, $serviceName)
    {
        return $query->when($serviceName, function ($q) use ($serviceName) {
            $q->whereHas('items', fn ($i) => $i->where('ServiceName', $serviceName));
        });
    }

    public function scopePaymentDateBetween($query, $from, $to)
    {
        return $query->when($from, fn ($q) => $q->whereDate('PaymentDate', '>=', $from))
                     ->when($to,   fn ($q) => $q->whereDate('PaymentDate', '<=', $to));
    }

    public function scopeExcludeCancelledRefunded($query)
    {
        return $query->whereNotIn('payment_status', ['cancelled', 'refunded']);
    }

    public function scopeSearchTerm($query, $term)
    {
        return $query->when($term, function ($q) use ($term) {
            $like = "%{$term}%";
            $q->where(function ($q2) use ($like) {
                $q2->where('PatientName', 'like', $like)
                   ->orWhere('PatientCode', 'like', $like)
                   ->orWhere('MobileNo', 'like', $like)
                   ->orWhere('BillNo', 'like', $like);
            });
        });
    }
}