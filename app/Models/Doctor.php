<?php
// app/Models/Doctor.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    protected $table = 'doctors';

    protected $fillable = [
        'reg_no',
        'doctor_name',
        'speciality',
        'contact',
        'Posting',
        'RateCode',
        'active',
        'user_id',
    ];

    // ডিফল্ট মান সেট করুন
    protected $attributes = [
        'active' => 1,
    ];

    public $timestamps = true;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function usgReports(): HasMany
    {
        return $this->hasMany(UsgReport::class, 'doctor_id');
    }

    public function followups(): HasMany
    {
        return $this->hasMany(UsgFollowup::class, 'doctor_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(DoctorNotification::class, 'doctor_id');
    }
}