<?php

// app/Models/CommonMedicine.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * CommonMedicine represents the master list of medicines.
 *
 * Note on database columns:
 * - medicine_type_id, medicine_sub_type_id, dosage_form_id and generic_name
 *   are expected to exist on the common_medicine table per the existing schema.
 * - If generic_name is not used in the current system, the controller/view hide it.
 */
class CommonMedicine extends Model
{
    protected $table = 'common_medicine';

    /**
     * Mass-assignable attributes.
     * Legacy columns (company, GroupName) are kept for backwards compatibility.
     */
    protected $fillable = [
        'code',
        'name',
        'GroupName',
        'company',
        'company_id',
        'strength',
        'name_normalized',
        'active',
        'medicine_type_id',
        'medicine_sub_type_id',
        'dosage_form_id',
        'generic_name',
        'note',
        'commonly_used',
    ];

    /* ── Relationships (used by the Medicine Master dropdowns) ── */

    public function medicineType()
    {
        return $this->belongsTo(MedicineType::class);
    }

    public function medicineSubType()
    {
        return $this->belongsTo(MedicineSubtype::class, 'medicine_sub_type_id');
    }

    public function companyRecord()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function dosageForm()
    {
        return $this->belongsTo(DosageForm::class, 'dosage_form_id');
    }
}
