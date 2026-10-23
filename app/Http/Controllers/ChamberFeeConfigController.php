<?php
namespace App\Http\Controllers\Billing;
use App\Http\Controllers\Controller;
use App\Models\ChamberFeeConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class ChamberFeeConfigController extends Controller
{
    public function index()
    {
        $configs = ChamberFeeConfig::orderBy('chamber_name')->paginate(10);
        return view('settings.chamber.chamber-fee-configs', compact('configs'));
    }
    public function store(Request $request)
    {
        $this->ajaxGuard();
        $validator = Validator::make($request->all(), [
            'chamber_name'    => 'required|string|max:100|unique:chamber_fee_configs,chamber_name',
            'first_visit_fee' => 'required|numeric|min:0',
            'followup_fee'    => 'required|numeric|min:0',
            'expired_fee'     => 'required|numeric|min:0',
            'followup_days'   => 'required|integer|min:1',
            'status'          => 'nullable|in:0,1',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $config = ChamberFeeConfig::create([
            'chamber_name'    => $request->chamber_name,
            'first_visit_fee' => $request->first_visit_fee,
            'followup_fee'    => $request->followup_fee,
            'expired_fee'     => $request->expired_fee,
            'followup_days'   => $request->followup_days,
            'status'          => $request->input('status', 1),
        ]);
        return response()->json(['success' => true, 'message' => 'Chamber fee config created.', 'data' => $config]);
    }
    public function update(Request $request, $id)
    {
        $this->ajaxGuard();
        $config = ChamberFeeConfig::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'chamber_name'    => 'required|string|max:100|unique:chamber_fee_configs,chamber_name,' . $id,
            'first_visit_fee' => 'required|numeric|min:0',
            'followup_fee'    => 'required|numeric|min:0',
            'expired_fee'     => 'required|numeric|min:0',
            'followup_days'   => 'required|integer|min:1',
            'status'          => 'nullable|in:0,1',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $config->update($request->only([
            'chamber_name', 'first_visit_fee', 'followup_fee', 'expired_fee', 'followup_days', 'status'
        ]));
        return response()->json(['success' => true, 'message' => 'Chamber fee config updated.', 'data' => $config]);
    }
    public function toggleStatus($id)
    {
        $this->ajaxGuard();
        $config = ChamberFeeConfig::findOrFail($id);
        $config->status = $config->status ? 0 : 1;
        $config->save();
        return response()->json(['success' => true, 'status' => $config->status]);
    }
    public function destroy($id)
    {
        $this->ajaxGuard();
        if (!RoleCheck(99)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        ChamberFeeConfig::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Deleted.']);
    }
    private function ajaxGuard()
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized action.');
        }
    }
}