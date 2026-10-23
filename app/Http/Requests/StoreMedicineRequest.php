<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Shared validation for medicine store + update.
     * Strength & Note are OPTIONAL.
     */
    public function rules(): array
    {
        return [
            'medicine_type_id'    => 'required|exists:medicine_types,id',
            'medicine_subtype_id' => [
                'nullable',
                Rule::exists('medicine_subtypes', 'id')
                    ->where('medicine_type_id', $this->input('medicine_type_id')),
            ],
            'company_id'          => 'required|exists:companies,id',
            'medicine_name'       => 'required|string|max:255',
            'strength'            => 'nullable|string|max:50',
            'dosage_form_id'      => 'required|exists:dosage_forms,id',
            'generic_name'        => 'nullable|string|max:255',
            'note'                => 'nullable|string|max:255',
            'status'              => 'required|in:0,1',
            'commonly_used'       => 'nullable|in:0,1',
        ];
    }

    public function attributes(): array
    {
        return [
            'medicine_type_id'    => 'medicine type',
            'medicine_subtype_id' => 'sub medicine type',
            'company_id'          => 'company',
            'medicine_name'       => 'medicine name',
            'dosage_form_id'      => 'dosage form',
            'generic_name'        => 'generic name',
            'note'                => 'note',
        ];
    }
}
