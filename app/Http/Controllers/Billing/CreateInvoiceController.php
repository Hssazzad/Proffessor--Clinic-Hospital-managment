<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\CreateInvoiceRequest;
use App\Services\Billing\CreateInvoiceFormDataService;
use App\Services\Billing\InvoiceService;
use App\Services\Billing\TemporaryBillService;
use App\Services\Billing\ConsultationFeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
    public function index(CreateInvoiceFormDataService $formData)
    {
        return view('billing.create_invoice_index', $formData->formData());
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
        $q = trim((string) $request->input('q', ''));

        $query = DB::table('investigation_payments')
            ->select(
                'ID',
                'BillNo',
                'PatientId',
                'PatientCode',
                'PatientName',
                'MobileNo',
                'TotalBill',
                'Discount',
                'PaidAmount',
                'DueAmount',
                'PaymentDate',
                'Status'
            );

        if (Schema::hasColumn('investigation_payments', 'InvoiceType')) {
            $query->addSelect('InvoiceType');
        } else {
            $query->addSelect(DB::raw("'' as InvoiceType"));
        }

        if (Schema::hasColumn('investigation_payments', 'approval_status')) {
            $query->addSelect(DB::raw("CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END as pending_count"));
        } else {
            $query->addSelect(DB::raw('0 as pending_count'));
        }

        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($sub) use ($like) {
                $sub->where('BillNo', 'like', $like)
                    ->orWhere('PatientName', 'like', $like)
                    ->orWhere('PatientCode', 'like', $like)
                    ->orWhere('MobileNo', 'like', $like);
            });
        }

        if (! $request->boolean('show_all')) {
            $query->where('DueAmount', '>', 0);
        }

        $paginated = $query->orderByDesc('ID')->paginate($request->input('per_page', 20));

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
                'message' => 'Invoice could not be saved. Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // CONSULTATION FEE CHECK
    // ============================================================
    public function checkConsultationFee(Request $request)
    {
        $request->validate([
            'patient_id'            => 'required|integer',
            'doctor_id'             => 'required|integer',
            'chamber_fee_config_id' => 'required|integer|exists:chamber_fee_configs,id',
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

            $result = $this->consultationFeeService->calculate(
                (int) $request->patient_id,
                (int) $request->doctor_id,
                (int) $request->chamber_fee_config_id
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
        // Read-only: forward the copy label, never re-create the invoice.
        $copy = strtolower((string) request()->query('copy', 'patient'));
        if (! in_array($copy, ['patient', 'office', 'reprint'], true)) {
            $copy = 'patient';
        }

        return redirect()->route('billing.invoice.pdf', ['id' => $id, 'copy' => $copy]);
    }

    public function downloadPdf($id)
    {
        $data = $this->loadInvoiceData($id)['data'];
        $payment = $data['payment'];

        $copyType = strtolower((string) request()->query('copy', 'patient'));
        if (! in_array($copyType, ['patient', 'office', 'reprint'], true)) {
            $copyType = 'patient';
        }
        $data['copyType'] = $copyType;

        $html = view('billing.invoice_print_pdf', $data)->render();

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
        $stampWord = strtoupper(strtolower($statusVal));

        if (($payment->approval_status ?? 'approved') === 'pending') {
            $stampWord = 'PENDING APPROVAL';
        }

        $mpdf->SetWatermarkText($stampWord);
        $mpdf->watermarkTextAlpha = 0.06;
        $mpdf->watermarkAngle     = -26;
        $mpdf->showWatermarkText  = true;

        $mpdf->WriteHTML($html);

        $billNo  = data_get($payment, 'BillNo') ?? data_get($payment, 'bill_no', $id);
        $filename = $billNo . '.pdf';

        $content = $mpdf->Output('', 'S');

        return response($content)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->header('Cache-Control', 'public, must-revalidate, max-age=0')
            ->header('Pragma', 'public')
            ->header('Expires', '0')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Content-Length', strlen($content));
    }

    protected function loadInvoiceData($id)
    {
        $payment = DB::table('investigation_payments')->where('ID', $id)->first();

        if (!$payment) {
            abort(404, 'Invoice not found.');
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

        // ============================================================
        // Clinic info (name/address/phone/delivery note) — dynamic,
        // pulled from the clinic_settings table if it exists, falling
        // back to config('clinic.*') so nothing is hardcoded in the view.
        // ============================================================
        $clinic = $this->getClinicSettings();

        return [
            'data' => compact('payment', 'items', 'consultantName', 'patient', 'patientcode', 'admission', 'medicine', 'medicines', 'printDoctor', 'clinic'),
        ];
    }
    protected function getClinicSettings()
    {
        try {
            $row = DB::table('clinic_settings')->first();

            if ($row) {
                return $row;
            }
        } catch (\Throwable $e) {
            \Log::warning('clinic_settings table not found, falling back to config: ' . $e->getMessage());
        }

        return (object) [
            'Address'      => config('clinic.address'),
            'Phone'        => config('clinic.phone'),
            'DeliveryNote' => config('clinic.delivery_note'),
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

        $references = DB::table('patient_ref')
            ->where('active', 1)
            ->orderBy('Name')
            ->get(['ID', 'Name', 'ref_type']);

        return view('billing.create_invoice_show', compact('patient', 'admission', 'references'));
    }
}