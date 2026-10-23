<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Discharge extends Model
{
    protected $table = 'discharges';

    protected $fillable = [
        'patient_id',
        'admission_id',
        'discharge_date',
        'diagnosis',
        'doctor_name',
        'ward',
        'reg_no',
        'operation_name',
        'operation_method',
        'surgeon',
        'anesthetist',
        'assistant_1',
        'assistant_2',
        'investigations',
        'blood_group',
        'drug_allergy',
        'notes',
        'follow_up_date',
        'status',
        'discharged_by',
        'operation_name_id',
        'surgeon_id',
        'anesthesiologist_id',
        'assistant_surgeon_1_id',
        'assistant_surgeon_2_id',
        'operation_date',
        'operation_time',
        'anesthesia_type',
        'discharge_diagnosis_id',
        'discharge_instruction_id',
        'operation_method_id',
    ];

    public function dischargeDiagnosis(): BelongsTo
    {
        return $this->belongsTo(DischargeDiagnosis::class, 'discharge_diagnosis_id');
    }

    public function dischargeInstruction(): BelongsTo
    {
        return $this->belongsTo(DischargeInstruction::class, 'discharge_instruction_id');
    }

    public function operationMethod(): BelongsTo
    {
        return $this->belongsTo(OperationMethod::class, 'operation_method_id');
    }

    public function operationName(): BelongsTo
    {
        return $this->belongsTo(OperationName::class, 'operation_name_id');
    }

    public function surgeon(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'surgeon_id');
    }

    public function anesthesiologist(): BelongsTo
    {
        return $this->belongsTo(Anesthesiologist::class, 'anesthesiologist_id');
    }

    public function assistantSurgeon1(): BelongsTo
    {
        return $this->belongsTo(AssistantSurgeon::class, 'assistant_surgeon_1_id');
    }

    public function assistantSurgeon2(): BelongsTo
    {
        return $this->belongsTo(AssistantSurgeon::class, 'assistant_surgeon_2_id');
    }
}