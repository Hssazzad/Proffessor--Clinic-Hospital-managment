<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\ConsultationFeeService;
use Illuminate\Http\Request;

class ConsultationFeeController extends Controller
{
    public function __construct(
        protected ConsultationFeeService $consultationFeeService
    ) {}

    public function check(Request $request)
    {
        $request->validate([
            'patient_id'            => 'required|integer|exists:patients,id',
            'doctor_id'             => 'required|integer|exists:doctors,id',
            'chamber_fee_config_id' => 'required|integer|exists:chamber_fee_configs,id',
        ]);

        $result = $this->consultationFeeService->calculate(
            (int) $request->patient_id,
            (int) $request->doctor_id,
            (int) $request->chamber_fee_config_id
        );

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }
}
