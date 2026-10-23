<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreConAssessment extends Model
{
    use HasFactory;

    // ১. টেবিলের নাম
    protected $table = 'preconassessment';

    // ২. প্রাইমারি কি (নিশ্চিত করুন আপনার ডাটাবেসে ID বড় হাতের অক্ষরে কি না)
    protected $primaryKey = 'ID';

    // ৩. টাইমস্ট্যাম্প
    public $timestamps = true;

    // ৪. ইনসার্ট করার যোগ্য কলামগুলো
    protected $fillable = [
        'patientcode', 
        'patient_id',
        'status',
        'weight',
        'height',
        'temp',
        'bp_sys',
        'bp_dia',
        'pulse',
        'spo2',
        'rr',
        'notes',
        'code',  // <--- এটি অবশ্যই যোগ করতে হবে
        'value'  // <--- এটি অবশ্যই যোগ করতে হবে
    ];

    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * পেশেন্টের সাথে রিলেশন
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patientcode', 'patientcode');
    }
}