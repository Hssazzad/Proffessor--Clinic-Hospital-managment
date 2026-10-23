<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuickAddMedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quick Add: Type + Company + Name + Dosage Form required. Strength optional.
     */
    public function rules(): array
    {
        return [
            'medicine_name'       => 'required|string|max:255',
            'dosage_form_id'      => 'required|exists:dosage_forms,id',
            'medicine_type_id'    => 'required|exists:medicine_types,id',
            'company_id'          => 'required|exists:companies,id',
            'medicine_subtype_id' => [
                'nullable',
                Rule::exists('medicine_subtypes', 'id')
                    ->where('medicine_type_id', $this->input('medicine_type_id')),
            ],
            'strength'            => 'nullable|string|max:50',
            'note'                => 'nullable|string|max:255',
        ];
    }

    public function attributes(): array
    {
        return [
            'medicine_type_id'    => 'medicine type',
            'company_id'          => 'company',
            'medicine_subtype_id' => 'sub medicine type',
            'dosage_form_id'      => 'dosage form',
            'note'                => 'note',
        ];
    }
}
