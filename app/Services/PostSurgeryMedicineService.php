<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\CommonMedicine;

class PostSurgeryMedicineService
{
    /**
     * Fetch available medicines from common_medicine table for post surgery
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableMedicinesForPostSurgery()
    {
        return CommonMedicine::where('active', 1)
            ->select('id', 'name', 'code', 'GroupName', 'strength')
            ->orderBy('GroupName')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get medicines from template (existing functionality)
     * 
     * @param string $templateId
     * @return \Illuminate\Support\Collection
     */
    public function getTemplateMedicines($templateId)
    {
        return DB::table('template_medicine')
            ->where('templeteid', $templateId)
            ->where('active', 1)
            ->orderBy('group')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get medicines from template for post surgery (only order_type = 'postorder')
     * This is used for auto-loading selected medicines when template is applied
     * 
     * @param string $templateId
     * @return \Illuminate\Support\Collection
     */
    public function getTemplateMedicinesForPostSurgery($templateId)
    {
        return DB::table('template_medicine')
            ->where('templeteid', $templateId)
            ->where('active', 1)
            ->where('order_type', 'postorder')
            ->orderBy('group')
            ->orderBy('name')
            ->get();
    }

    /**
     * Apply template and map medicines to common_medicine data
     * 
     * @param string $templateId
     * @return array
     */
    public function applyTemplateWithCommonMedicines($templateId)
    {
        // Get template medicines for auto-loading (only order_type = 'postorder')
        $templateMedicines = $this->getTemplateMedicinesForPostSurgery($templateId);
        
        // Get all available common medicines for reference
        $commonMedicines = $this->getAvailableMedicinesForPostSurgery();
        
        // Create a lookup map for common medicines
        $medicineMap = $commonMedicines->keyBy(function($item) {
            return strtolower(trim($item->name));
        });
        
        // Map template medicines to common medicine format
        $mappedMedicines = $templateMedicines->map(function($med) use ($medicineMap) {
            $medicineName = trim($med->name ?? $med->group ?? '');
            if (empty($medicineName)) {
                return null;
            }
            
            // Try to find matching common medicine
            $commonMed = $medicineMap->get(strtolower($medicineName));
            
            return [
                'medicine_name' => $medicineName,
                'dose'          => $med->dose ?? '',
                'route'         => $med->route ?? '',
                'frequency'     => $med->frequency ?? '',
                'duration'      => $med->duration ?? '',
                'timing'        => !empty($med->timing) ? $med->timing : ($med->meal_timing ?? ''),
                'remarks'       => $med->note ?? '',
                'common_medicine_id' => $commonMed->id ?? null,
                'medicine_code' => $commonMed->code ?? '',
                'group_name'    => $commonMed->GroupName ?? '',
                'strength'      => $commonMed->strength ?? '',
                'is_from_common' => !is_null($commonMed)
            ];
        })->filter()->values();
        
        return [
            'template_medicines' => $mappedMedicines,
            'available_medicines' => $commonMedicines,
            'template_medicines_count' => $mappedMedicines->count(),
            'available_medicines_count' => $commonMedicines->count()
        ];
    }

    /**
     * Search medicines from common_medicine table
     * 
     * @param string $query
     * @return \Illuminate\Support\Collection
     */
    public function searchCommonMedicines($query = '')
    {
        $queryBuilder = CommonMedicine::where('active', 1)
            ->select('id', 'name', 'code', 'GroupName', 'strength');
            
        if (!empty($query)) {
            $queryBuilder->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('code', 'like', "%{$query}%")
                  ->orWhere('GroupName', 'like', "%{$query}%");
            });
        }
        
        return $queryBuilder->orderBy('GroupName')
            ->orderBy('name')
            ->limit(100)
            ->get();
    }

    /**
     * Get medicine details by ID from common_medicine
     * 
     * @param int $medicineId
     * @return object|null
     */
    public function getCommonMedicineById($medicineId)
    {
        return CommonMedicine::where('active', 1)
            ->where('id', $medicineId)
            ->first();
    }

    /**
     * Print example output for testing
     * 
     * @return void
     */
    public function printExampleOutput()
    {
        echo "=== POST SURGERY MEDICINE SERVICE EXAMPLE OUTPUT ===\n\n";
        
        // Example 1: Get available medicines
        echo "1. Available Medicines for Post Surgery:\n";
        $medicines = $this->getAvailableMedicinesForPostSurgery();
        echo "Total medicines: " . $medicines->count() . "\n";
        echo "Sample medicines:\n";
        $medicines->take(3)->each(function($med) {
            echo "  - ID: {$med->id}, Name: {$med->name}, Code: {$med->code}, Group: {$med->GroupName}\n";
        });
        
        echo "\n2. Template Medicine Mapping (order_type = 'preorder'):\n";
        // Example template data simulation
        $exampleTemplateMedicines = collect([
            (object) [
                'name' => 'Ibuprofen',
                'dose' => '400mg',
                'route' => 'Oral',
                'frequency' => 'TID',
                'duration' => '3 days',
                'timing' => 'After meal',
                'note' => 'Post-op pain relief'
            ],
            (object) [
                'name' => 'Omeprazole',
                'dose' => '20mg',
                'route' => 'Oral',
                'frequency' => 'OD',
                'duration' => '5 days',
                'timing' => 'Before meal',
                'note' => 'Stomach protection'
            ]
        ]);
        
        $commonMedicines = $this->getAvailableMedicinesForPostSurgery();
        $medicineMap = $commonMedicines->keyBy(function($item) {
            return strtolower(trim($item->name));
        });
        
        $mappedMedicines = $exampleTemplateMedicines->map(function($med) use ($medicineMap) {
            $commonMed = $medicineMap->get(strtolower($med->name));
            return [
                'medicine_name' => $med->name,
                'dose' => $med->dose,
                'route' => $med->route,
                'frequency' => $med->frequency,
                'duration' => $med->duration,
                'timing' => $med->timing,
                'remarks' => $med->note,
                'common_medicine_id' => $commonMed->id ?? null,
                'is_from_common' => !is_null($commonMed)
            ];
        });
        
        echo "Mapped template medicines:\n";
        $mappedMedicines->each(function($med) {
            echo "  - {$med['medicine_name']}: " . ($med['is_from_common'] ? 'Found in common_medicine' : 'Not in common_medicine') . "\n";
        });
        
        echo "\n3. Search Example:\n";
        $searchResults = $this->searchCommonMedicines('Ibuprofen');
        echo "Search results for 'Ibuprofen': {$searchResults->count()} found\n";
        $searchResults->each(function($med) {
            echo "  - {$med->name} ({$med->code}) - {$med->GroupName}\n";
        });
        
        echo "\n=== END EXAMPLE OUTPUT ===\n";
    }
}
