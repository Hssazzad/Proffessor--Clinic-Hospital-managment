<?php

namespace App\Http\Controllers;

use App\Models\AssistantSurgeon;
use App\Models\Anesthesiologist;
use App\Models\OperationName;
use App\Models\DischargeDiagnosis;
use App\Models\DischargeInstruction;
use App\Models\OperationMethod;
use Illuminate\Http\Request;

class OperationStaffController extends Controller
{
    private function model(string $type)
    {
        return match ($type) {
            'assistant_surgeon'   => new AssistantSurgeon,
            'operation_name'      => new OperationName,
            'discharge_diagnosis' => new DischargeDiagnosis,
            'discharge_instruction'=> new DischargeInstruction,
            'operation_method'    => new OperationMethod,
            default               => new Anesthesiologist,
        };
    }

    private function viewType(string $type): array
    {
        if ($type === 'operation_name') {
            return $this->operationNameType();
        }

        if ($type === 'discharge_diagnosis') {
            return $this->dischargeDiagnosisType();
        }

        if ($type === 'discharge_instruction') {
            return $this->dischargeInstructionType();
        }

        if ($type === 'operation_method') {
            return $this->operationMethodType();
        }

        if ($type === 'assistant_surgeon') {
            return [
                'title'   => 'Assistant Surgeons',
                'heading' => 'Assistant Surgeons',
                'singular'=> 'Assistant Surgeon',
                'index'   => 'settings.assistant_surgeons.index',
                'create'  => 'settings.assistant_surgeons.create',
                'store'   => 'settings.assistant_surgeons.store',
                'edit'    => 'settings.assistant_surgeons.edit',
                'update'  => 'settings.assistant_surgeons.update',
                'destroy' => 'settings.assistant_surgeons.destroy',
                'data'    => 'settings.assistant_surgeons.data',
            ];
        }

        return [
            'title'   => 'Anesthesiologists',
            'heading' => 'Anesthesiologists',
            'singular'=> 'Anesthesiologist',
            'index'   => 'settings.anesthesiologists.index',
            'create'  => 'settings.anesthesiologists.create',
            'store'   => 'settings.anesthesiologists.store',
            'edit'    => 'settings.anesthesiologists.edit',
            'update'  => 'settings.anesthesiologists.update',
            'destroy' => 'settings.anesthesiologists.destroy',
            'data'    => 'settings.anesthesiologists.data',
        ];
    }

    private function operationNameType(): array
    {
        return [
            'title'   => 'Operation Names',
            'heading' => 'Operation Names',
            'singular'=> 'Operation Name',
            'index'   => 'settings.operation_names.index',
            'create'  => 'settings.operation_names.create',
            'store'   => 'settings.operation_names.store',
            'edit'    => 'settings.operation_names.edit',
            'update'  => 'settings.operation_names.update',
            'destroy' => 'settings.operation_names.destroy',
            'data'    => 'settings.operation_names.data',
        ];
    }

    private function dischargeDiagnosisType(): array
    {
        return [
            'title'   => 'Discharge Diagnoses',
            'heading' => 'Discharge Diagnoses',
            'singular'=> 'Discharge Diagnosis',
            'index'   => 'settings.discharge_diagnoses.index',
            'create'  => 'settings.discharge_diagnoses.create',
            'store'   => 'settings.discharge_diagnoses.store',
            'edit'    => 'settings.discharge_diagnoses.edit',
            'update'  => 'settings.discharge_diagnoses.update',
            'destroy' => 'settings.discharge_diagnoses.destroy',
            'data'    => 'settings.discharge_diagnoses.data',
        ];
    }

    private function dischargeInstructionType(): array
    {
        return [
            'title'   => 'Discharge Instructions',
            'heading' => 'Discharge Instructions',
            'singular'=> 'Discharge Instruction',
            'index'   => 'settings.discharge_instructions.index',
            'create'  => 'settings.discharge_instructions.create',
            'store'   => 'settings.discharge_instructions.store',
            'edit'    => 'settings.discharge_instructions.edit',
            'update'  => 'settings.discharge_instructions.update',
            'destroy' => 'settings.discharge_instructions.destroy',
            'data'    => 'settings.discharge_instructions.data',
        ];
    }

    private function operationMethodType(): array
    {
        return [
            'title'   => 'Operation Methods',
            'heading' => 'Operation Methods',
            'singular'=> 'Operation Method',
            'index'   => 'settings.operation_methods.index',
            'create'  => 'settings.operation_methods.create',
            'store'   => 'settings.operation_methods.store',
            'edit'    => 'settings.operation_methods.edit',
            'update'  => 'settings.operation_methods.update',
            'destroy' => 'settings.operation_methods.destroy',
            'data'    => 'settings.operation_methods.data',
        ];
    }

    public function index($type)
    {
        return view('settings.operation_staff.index', array_merge(
            $this->viewType($type),
            ['type' => $type]
        ));
    }

    public function ajaxList(Request $r, $type)
    {
        $query = $this->model($type)->query();

        if ($r->filled('search')) {
            $query->where('name', 'like', '%' . $r->search . '%');
        }

        $items = $query->orderByDesc('id')->get(['id', 'name', 'active']);

        return response()->json(['ok' => true, 'data' => $items]);
    }

    public function create($type)
    {
        return view('settings.operation_staff.create', array_merge(
            $this->viewType($type),
            ['type' => $type]
        ));
    }

    public function store(Request $r, $type)
    {
        $r->validate(['name' => 'required|string|max:255']);

        $item = $this->model($type)->create([
            'name'   => $r->name,
            'active' => $r->has('active') ? $r->active : 1,
        ]);

        if ($r->ajax() || $r->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Name added successfully!', 'data' => $item]);
        }

        return redirect()
            ->route($this->viewType($type)['index'])
            ->with('success', 'Name added successfully!');
    }

    public function edit($id, $type)
    {
        $item = $this->model($type)->findOrFail($id);

        return view('settings.operation_staff.edit', array_merge(
            $this->viewType($type),
            ['type' => $type, 'item' => $item]
        ));
    }

    public function update(Request $r, $id, $type)
    {
        $r->validate(['name' => 'required|string|max:255']);

        $item = $this->model($type)->findOrFail($id);
        $item->update([
            'name'   => $r->name,
            'active' => $r->has('active') ? $r->active : 1,
        ]);

        if ($r->ajax() || $r->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Name updated successfully!', 'data' => $item]);
        }

        return redirect()
            ->route($this->viewType($type)['index'])
            ->with('success', 'Name updated successfully!');
    }

    public function destroy(Request $r, $id, $type)
    {
        $this->model($type)->findOrFail($id)->delete();

        if ($r->ajax() || $r->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Name deleted successfully!']);
        }

        return redirect()
            ->route($this->viewType($type)['index'])
            ->with('success', 'Name deleted successfully!');
    }
}
