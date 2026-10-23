<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\CreateInvoiceRequest;
use App\Models\ChamberFeeConfig;
use App\Services\Billing\InvoiceService;
use App\Services\Billing\TemporaryBillService;
use App\Services\Billing\ConsultationFeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;

class CreateInvoiceController extends Controller
{
    protected InvoiceService $invoiceService;
    protected TemporaryBillService $tempBillService;
    protected ConsultationFeeService $consultationFeeService;

    public function __construct(
        InvoiceService $invoiceService,
        TemporaryBillService $tempBillService,
        ConsultationFeeService $consultationFeeService
    ) {
        $this->invoiceService = $invoiceService;
        $this->tempBillService = $tempBillService;
        $this->consultationFeeService = $consultationFeeService;
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

    // ============================================================
    // Chamber Types (dynamic — from chamber_fee_configs table)
    // ============================================================
    public function getChamberTypes()
    {
        $data = DB::table('chamber_fee_configs')
            ->select('id', 'chamber_name')
            ->where('status', 1)
            ->orderBy('chamber_name')
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
    // CONSULTATION FEE CHECK
    // ============================================================
    public function checkConsultationFee(Request $request)
    {
        $request->validate([
            'patient_id'   => 'required|integer',
            'doctor_id'    => 'required|integer',
            'chamber_type' => 'required|string|exists:chamber_fee_configs,chamber_name',
        ]);

        try {

            $patientCode = DB::table('patients')
                ->where('id', $request->patient_id)
                ->value('patientcode');

            if ($patientCode) {
                $alreadyAdded = DB::table('tbl_bill_tmp')
                    ->where('PatientCode', $patientCode)
                    ->where('DoctorId', $request->doctor_id)
                    ->exists();

                if ($alreadyAdded) {
                    return response()->json([
                        'already_added' => true,
                        'message'       => 'Doctor consultation already added.',
                    ]);
                }
            }

            $configId = ChamberFeeConfig::where('chamber_name', $request->chamber_type)
                ->where('status', 1)
                ->value('id');

            if (!$configId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active chamber fee configuration found for "' . $request->chamber_type . '".'
                ], 422);
            }

            $result = $this->consultationFeeService->calculate(
                (int) $request->patient_id,
                (int) $request->doctor_id,
                (int) $configId
            );

            return response()->json($result);

        } catch (\Throwable $e) {

            \Log::error('Consultation Fee Error : '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to calculate consultation fee.'
            ], 500);
        }
    }

    // ============================================================
    // PRINT & SHOW
    // ============================================================
    public function printInvoice($id)
    {
        $loaded = $this->loadInvoiceData($id);

        if (isset($loaded['blocked'])) {
            return view('billing.invoice_blocked', $loaded['blocked']);
        }

        return view('billing.invoice_print', $loaded['data']);
    }

    public function downloadPdf($id)
    {
        $loaded = $this->loadInvoiceData($id);

        if (isset($loaded['blocked'])) {
            return view('billing.invoice_blocked', $loaded['blocked']);
        }

        $data = $loaded['data'];
        $payment = $data['payment'];

        $html = (string) view('billing.invoice_print_pdf', $data)->render();

        if (trim($html) === '') {
            abort(500, 'Invoice HTML is empty, cannot generate PDF.');
        }

        $fontDirs = (new ConfigVariables())->getDefaults()['fontDir'];
        $fontData = (new FontVariables())->getDefaults()['fontdata'];

        $mpdf = new Mpdf([
            'mode'              => 'utf-8',
            'format'            => [148, 210],
            'orientation'       => 'P',
            'margin_left'       => 0,
            'margin_right'      => 0,
            'margin_top'        => 0,
            'margin_bottom'     => 0,

            'fontDir' => array_merge($fontDirs, [base_path('public/fonts')]),

            'fontdata' => $fontData + [
                'notobengali' => [
                    'R'  => 'NotoSansBengali-Regular.ttf',
                    'B'  => 'NotoSansBengali-Bold.ttf',
                    'I'  => 'NotoSansBengali-Regular.ttf',
                    'BI' => 'NotoSansBengali-Bold.ttf',
                ],
            ],

            'default_font' => 'notobengali',

            'useOTL'           => 1,
            'autoScriptToLang' => true,
            'autoLangToFont'   => true,
        ]);

        $statusVal = data_get($payment, 'Status') ?? data_get($payment, 'status', 'due');
        $stampWord = trim((string) $statusVal);
        $stampWord = $stampWord !== '' ? strtoupper($stampWord) : 'DUE';

        $mpdf->SetWatermarkText($stampWord);
        $mpdf->watermarkTextAlpha = 0.06;
        $mpdf->watermarkAngle     = -26;
        $mpdf->showWatermarkText  = true;

        try {
            $mpdf->WriteHTML($html);
        } catch (\Throwable $e) {
            \Log::error('CreateInvoiceController@downloadPdf WriteHTML failed: ' . $e->getMessage(), [
                'invoice_id' => $id,
            ]);
            abort(500, 'Failed to render invoice PDF. Please check the invoice template for malformed HTML.');
        }

        $billNo  = data_get($payment, 'BillNo') ?? data_get($payment, 'bill_no', $id);
        $unique  = date('Ymd_His') . '_' . strtoupper(substr(uniqid(), -4));
        $filename = 'Invoice_' . $billNo . '_' . $unique . '.pdf';

        return response($mpdf->Output($filename, 'S'))
            ->header('Content-Type', 'application/pdf');
    }

    protected function loadInvoiceData($id)
    {
        $payment = DB::table('investigation_payments')->where('ID', $id)->first();

        if (!$payment) {
            abort(404, 'Invoice not found.');
        }

        $approvalStatus = $payment->approval_status ?? 'approved';

        if ($approvalStatus === 'pending' || $approvalStatus === 'rejected') {
            return [
                'blocked' => [
                    'payment' => $payment,
                    'reason'  => $approvalStatus,
                ],
            ];
        }

        $items = DB::table('investigation_payment_items as ipi')
            ->leftJoin('doctors as d', 'd.id', '=', 'ipi.DoctorId')
            ->where('ipi.PaymentId', $id)
            ->select('ipi.*', 'd.doctor_name as RefDoctorName')
            ->get();

        $consultantName = $items
            ->where('CategoryName', 'Doctor Consultation')
            ->sortBy('ID')
            ->pluck('ServiceName')
            ->filter()
            ->first();

        $patient = DB::table('patients')->where('id', $payment->PatientId)->first()
            ?? (object) [
                'patientname' => $payment->PatientName,
                'mobile_no'   => $payment->MobileNo,
            ];
        $patientcode = $payment->PatientCode ?? ($patient->patientcode ?? '');
        $admission = $payment->PatientId
            ? DB::table('nursing_admissions')->where('patient_id', $payment->PatientId)->orderByDesc('id')->first()
            : null;
        $medicine = DB::table('common_medicine')->orderBy('name')->get();
        $medicines = collect();
        $printDoctor = null;

        return [
            'data' => compact('payment', 'items', 'consultantName', 'patient', 'patientcode', 'admission', 'medicine', 'medicines', 'printDoctor'),
        ];
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
?>