<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Patient extends Model
{
    use HasFactory;
    protected $table = 'patients';
    protected $primaryKey = 'id';
    protected $fillable = [
        // ✅ Required fields
        'patientcode',
        'registration_date',
        'patientname',
        'patientfather',
        'mobile_no',
        'date_of_birth',
        'age',
        'gender',
        // ✅ Optional personal info
        'patienthusband',
        'photo',
        'nid_number',
        'email',
        // ✅ Optional contact
        'spomobile_no',
        'relmobile_no',
        // ✅ Optional address
        'district',
        'upozila',
        'union',
        'village',
        'post',
        'address',
        // ✅ Optional health info
        'blood_group',
        // ✅ Optional reference
        'reference_type',
        'reference_person',
        'reference_name',
        // ✅ Optional notes
        'notes',
    ];
    protected $casts = [
        'date_of_birth'     => 'date',
        'registration_date' => 'datetime',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    // =====================
    // Relationships
    // =====================

    public function admissions()
    {
        return $this->hasMany('App\Models\NursingAdmission', 'patient_id', 'id');
    }

    public function latestAdmission()
    {
        return $this->hasOne('App\Models\NursingAdmission', 'patient_id', 'id')
                    ->latest('id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'patient_id', 'id');
    }

    // =====================
    // Accessors
    // =====================

    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->village,
            $this->post,
            $this->union,
            $this->upozila,
            $this->district,
        ]);
        return implode(', ', $parts) ?: 'N/A';
    }

    public function getFormattedMobileAttribute()
    {
        return $this->mobile_no ? '+88' . substr($this->mobile_no, 1) : 'N/A';
    }

    public function getStatusBadgeAttribute()
    {
        if (!$this->latestAdmission) {
            return ['text' => 'No Admission', 'class' => 'secondary'];
        }
        $status = $this->latestAdmission->status;
        return match ($status) {
            1 => ['text' => 'On Admission', 'class' => 'warning'],
            2 => ['text' => 'Post Surgery', 'class' => 'info'],
            3 => ['text' => 'Fresh', 'class' => 'success'],
            default => ['text' => 'Unknown', 'class' => 'secondary'],
        };
    }

    public function getCalculatedAgeAttribute()
    {
        if (!$this->date_of_birth) {
            return null;
        }
        $birthDate = $this->date_of_birth;
        $today = now();
        $years = $today->diffInYears($birthDate);
        $months = $today->copy()->subYears($years)->diffInMonths($birthDate);
        $days = $today->copy()->subYears($years)->subMonths($months)->diffInDays($birthDate);
        return "{$years} Years {$months} Months {$days} Days";
    }
}