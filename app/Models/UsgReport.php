<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UsgReport extends Model
{
    protected $table = 'usg_reports';

    protected $fillable = [
        'patient_id',
        'patientcode',
        'doctor_id',
        'report_date',
        'findings',
        'impression',
        'followup_date',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'followup_date' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function followup(): HasOne
    {
        return $this->hasOne(UsgFollowup::class, 'usg_report_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(DoctorNotification::class, 'usg_followup_id', 'id');
    }
}
