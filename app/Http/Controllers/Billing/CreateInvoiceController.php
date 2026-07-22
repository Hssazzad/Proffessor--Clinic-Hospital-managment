<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\CreateInvoiceRequest;
use App\Services\Billing\InvoiceService;
use App\Services\Billing\TemporaryBillService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreateInvoiceController extends Controller
{
    protected InvoiceService $invoiceService;
    protected TemporaryBillService $tempBillService;

    public function __construct(
        InvoiceService $invoiceService,
        TemporaryBillService $tempBillService
    ) {
        $this->invoiceService = $invoiceService;
        $this->tempBillService = $tempBillService;
    }

    // ============================================================
    // GET /Billing/CreateInvoice
    // ============================================================
    public function index()
    {
        $invTypes = DB::table('configMain')
            ->select('Code', 'Name')
            ->orderBy('Name')
            ->get();

        return view('billing.create_invoice_index', compact('invTypes'));
    }

    // ============================================================
    // AJAX ENDPOINTS
    // ============================================================
    public function getBillTypes()
    {
        $fallback = [
            ['value' => 'doctor_visit', 'label' => 'Doctor Visit', 'icon' => 'fa-user-md', 'color' => '#2563eb', 'main_code' => null, 'source' => 'fallback', 'requires_doctor' => true, 'requires_category' => false, 'free_text_items' => false],
            ['value' => 'investigation', 'label' => 'Investigation', 'icon' => 'fa-microscope', 'color' => '#059669', 'main_code' => null, 'source' => 'fallback', 'requires_doctor' => false, 'requires_category' => true, 'free_text_items' => false],
            ['value' => 'full_bill',     'label' => 'Full Bill',     'icon' => 'fa-file-invoice', 'color' => '#dc2626', 'main_code' => null, 'source' => 'fallback', 'requires_doctor' => false, 'requires_category' => false, 'free_text_items' => true],
        ];

        try {
            $rows = DB::table('bill_types')
                ->where('is_active', 1)
                ->orderBy('sort_order')
                ->get();

            if ($rows->isEmpty()) {
                return response()->json($fallback);
            }

            $types = $rows->map(fn($row) => [
                'value'             => $row->slug,
                'label'             => $row->name,
                'icon'              => $row->icon,
                'color'             => $row->color,
                'main_code'         => $row->main_code,
                'source'            => 'bill_types',
                'requires_doctor'   => (bool) $row->requires_doctor,
                'requires_category' => (bool) $row->requires_category,
                'free_text_items'   => (bool) $row->free_text_items,
            ]);

            return response()->json($types);

        } catch (\Throwable $e) {
            \Log::warning('bill_types table not found, using fallback: ' . $e->getMessage());
            return response()->json($fallback);
        }
    }

    public function getMain()
    {
        $data = DB::table('configMain')
            ->select('Code', 'Name')
            ->orderBy('Name')
            ->get();

        return response()->json($data);
    }

    public function getSub(Request $request)
    {
        $data = DB::table('configSub')
            ->select('Code', 'MainCode', 'Name', 'Amount')
            ->where('MainCode', $request->input('main_code'))
            ->orderBy('Name')
            ->get();

        return response()->json($data);
    }

    public function getDoctors()
    {
        $data = DB::table('doctors')
            ->select('id', 'doctor_name as name', 'speciality', 'contact')
            ->where('active', 1)
            ->orderBy('doctor_name')
            ->get();

        return response()->json($data);
    }

    public function searchPatient(Request $request)
    {
        $q = '%' . $request->input('q', '') . '%';

        $data = DB::table('patients as p')
            ->leftJoin('nursing_admissions as na', function ($join) {
                $join->on('na.patient_id', '=', 'p.id')
                     ->whereRaw('na.id = (SELECT MAX(id) FROM nursing_admissions WHERE patient_id = p.id)');
            })
            ->select(
                'p.id', 
                'p.patientcode', 
                'p.patientname', 
                'p.age', 
                'p.gender', 
                'p.mobile_no', 
                'na.id as admission_id'
            )
            ->where(function ($query) use ($q) {
                $query->where('p.patientname', 'like', $q)
                      ->orWhere('p.patientcode', 'like', $q)
                      ->orWhere('p.mobile_no', 'like', $q);
            })
            ->orderByDesc('p.id')
            ->limit(20)
            ->get();

        return response()->json($data);
    }

    public function getCollectors()
    {
        try {
            $data = DB::table('patient_ref')
                ->select('Name')
                ->where('ref_type', 'OfficeEmployee')
                ->where('active', 1)
                ->orderBy('Name')
                ->get();

            return response()->json($data);
        } catch (\Throwable $e) {
            \Log::error('getCollectors: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    public function list(Request $request)
    {
        $query = DB::table('investigation_payments')
            ->select(
                'PatientId', 'PatientCode', 'PatientName', 'MobileNo',
                DB::raw('SUM(TotalBill) AS TotalBill'),
                DB::raw('SUM(Discount) AS Discount'),
                DB::raw('SUM(PaidAmount) AS PaidAmount'),
                DB::raw('SUM(DueAmount) AS DueAmount'),
                DB::raw('MAX(PaymentDate) AS PaymentDate'),
                DB::raw('MAX(ID) AS ID'),
                DB::raw('GROUP_CONCAT(BillNo ORDER BY ID SEPARATOR ", ") AS BillNo'),
                DB::raw('COUNT(*) AS invoice_count'),
                DB::raw("CASE 
                            WHEN SUM(DueAmount) <= 0 THEN 'paid' 
                            WHEN SUM(PaidAmount) > 0 THEN 'partial' 
                            ELSE 'due' 
                         END AS Status")
            )
            ->groupBy('PatientId', 'PatientCode', 'PatientName', 'MobileNo')
            ->when($request->q, function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('PatientName', 'like', '%' . $request->q . '%')
                       ->orWhere('PatientCode', 'like', '%' . $request->q . '%')
                       ->orWhere('MobileNo',    'like', '%' . $request->q . '%')
                       ->orWhere('BillNo',      'like', '%' . $request->q . '%');
                });
            })
            ->when(!$request->show_all, fn($q) => $q->having('DueAmount', '>', 0))
            ->orderByDesc('ID');

        $paginated = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $paginated->items(),
            'meta' => [
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'from'         => $paginated->firstItem(),
                'to'           => $paginated->lastItem(),
            ],
        ]);
    }

    // ====================== TEMPORARY BILL METHODS ======================
    public function addTmp(Request $request)
    {
        $result = $this->tempBillService->addTemporaryItem($request->all());
        return response()->json($result);
    }

    public function removeTmp(Request $request)
    {
        $success = $this->tempBillService->removeTemporaryItem((int) $request->input('tmp_id'));
        return response()->json(['success' => $success]);
    }

    public function clearTmp(Request $request)
    {
        $success = $this->tempBillService->clearTemporaryItems($request->input('patient_code'));
        return response()->json(['success' => $success]);
    }

    public function getTmp(Request $request)
    {
        $data = $this->tempBillService->getTemporaryItems($request->input('patient_code'));
        return response()->json($data);
    }

    // ====================== STORE INVOICE ======================
    public function store(CreateInvoiceRequest $request)
    {
        try {
            $result = $this->invoiceService->createInvoice($request);
            return response()->json($result);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);

        } catch (\Throwable $e) {
            \Log::error('CreateInvoiceController@store: ' . $e->getMessage(), [
                'patient_id' => $request->patient_id ?? null,
                'trace'      => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // PRINT & SHOW
    // ============================================================
    public function printInvoice($id)
    {
        $payment = DB::table('investigation_payments')->where('ID', $id)->first();
        
        if (!$payment) {
            abort(404, 'Invoice not found.');
        }

        $items = DB::table('investigation_payment_items')
            ->where('PaymentId', $id)
            ->get();

        return view('billing.invoice_print', compact('payment', 'items'));
    }

    public function show($patientId)
    {
        $patient = DB::table('patients')->where('id', $patientId)->first();
        if (!$patient) {
            abort(404);
        }

        $admission = DB::table('nursing_admissions')
            ->where('patient_id', $patientId)
            ->orderByDesc('id')
            ->first();

        return view('billing.create_invoice_show', compact('patient', 'admission'));
    }
}