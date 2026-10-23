<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admission extends Model
{
    use HasFactory;

    /**
     * টেবিলের নাম নিশ্চিত করা হলো।
     */
    protected $table = 'admissions';

    /**
     * প্রাইমারি কি।
     */
    protected $primaryKey = 'id';

    /**
     * Mass Assignment সুরক্ষা। 
     * আপনার কন্ট্রোলারের লজিক অনুযায়ী এটি খালি রাখা হয়েছে যাতে সব ডাটা সেভ হয়।
     */
    protected $guarded = [];

    /**
     * ডাটাবেস কলামগুলোকে নির্দিষ্ট ফরম্যাটে রূপান্তর (Casting)।
     * এটি করলে তারিখগুলো কার্বন অবজেক্ট হিসেবে কাজ করবে।
     */
    protected $casts = [
        'admission_date' => 'date',
        'discharge_date' => 'date',
        'op_date'        => 'date',
        'templateid'     => 'string',
        'age'            => 'integer',
    ];

    /**
     * আপনার কন্ট্রোলারের কোড অনুযায়ী প্রয়োজনীয় সকল কলামের তালিকা (Mass Assignment এর জন্য)।
     * $guarded = [] থাকা অবস্থায় এটি অপশনাল, তবে স্পষ্টভাবে লিখে রাখা ভালো।
     */
    protected $fillable = [
        'templateid',
        'admission_date',
        'discharge_date',
        'ward_no',
        'bed_no',
        'reg_no',
        'patient_name',
        'age',
        'father_husband_name',
        'village',
        'post_office',
        'police_station',
        'district',
        'adm_diagnosis',
        'operation_name',
        'op_date',
        'op_time',
        'surgeon_name',
        'assistant_surgeon',
        'anesthetist',
        'procedure_type_id',
    ];

    /**
     * টাইমস্ট্যাম্প এনাবল রাখা হয়েছে।
     */
    public $timestamps = true;

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patientcode', 'patientcode');
    }

    public function procedureType()
    {
        return $this->belongsTo(ProcedureType::class);
    }
}