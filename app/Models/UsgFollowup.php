<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UsgFollowup extends Model
{
    protected $table = 'usg_followups';

    protected $fillable = [
        'usg_report_id',
        'patient_id',
        'doctor_id',
        'followup_date',
        'status',
        'contacted_at',
        'contacted_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'followup_date' => 'date',
            'contacted_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(UsgReport::class, 'usg_report_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function contactedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contacted_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(DoctorNotification::class, 'usg_followup_id');
    }

    public function scopeActiveDue($query)
    {
        return $query->where('status', 'pending')
            ->whereDate('followup_date', '<=', now()->toDateString());
    }
}
