<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorNotification extends Model
{
    protected $table = 'doctor_notifications';

    protected $fillable = [
        'doctor_id',
        'usg_followup_id',
        'type',
        'title',
        'message',
        'is_read',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'notified_at' => 'datetime',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function followup(): BelongsTo
    {
        return $this->belongsTo(UsgFollowup::class, 'usg_followup_id');
    }
}
