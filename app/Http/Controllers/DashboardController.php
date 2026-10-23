<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // ===== Patients & Doctors =====
        $totalPatients = DB::table('patients')->count();

        $todayPatients = DB::table('investigation_payments')
            ->whereDate('PaymentDate', $today)
            ->distinct()
            ->count('PatientId');

        $todayRegistered = DB::table('patients')
            ->whereDate('registration_date', $today)
            ->count();

        $totalDoctors = DB::table('doctors')->count();
        $activeDoctors = DB::table('doctors')->where('active', 1)->count();

        // ===== Appointments & Vitals =====
        $todayAppointments = DB::table('appointments')
            ->whereDate('appointment_date', $today)
            ->count();

        $pendingAppointments = DB::table('appointments')
            ->where('status', 'pending')
            ->count();

        $todayPrescription = DB::table('prescriptions')
            ->whereDate('created_at', $today)
            ->count();

        $todayVitals = DB::table('preconassessment')
            ->whereDate('created_at', $today)
            ->count();

        $followupToday = DB::table('prescriptions')
            ->whereDate('next_appointment', $today)
            ->count();

        // ===== Financial (investigation_payment_transactions) =====
        $todayRevenue = (float) (DB::table('investigation_payment_transactions as t')
            ->join('investigation_payments as p', 'p.ID', '=', 't.invoice_id')
            ->whereDate('t.transaction_date', $today)
            ->where('t.type', 'cash')
            ->sum('t.amount') ?? 0);

        $yesterdayRevenue = (float) (DB::table('investigation_payment_transactions as t')
            ->join('investigation_payments as p', 'p.ID', '=', 't.invoice_id')
            ->whereDate('t.transaction_date', Carbon::yesterday())
            ->where('t.type', 'cash')
            ->sum('t.amount') ?? 0);

        $monthRevenue = DB::table('investigation_payments')
            ->whereMonth('PaymentDate', now()->month)
            ->whereYear('PaymentDate', now()->year)
            ->sum('PaidAmount');

        $totalRevenue = DB::table('investigation_payments')->sum('PaidAmount');

        $totalDue = DB::table('investigation_payments')
            ->where('DueAmount', '>', 0)
            ->where('approval_status', '!=', 'rejected')
            ->sum('DueAmount');

        $pendingApprovals = DB::table('investigation_payments')
            ->where('approval_status', 'pending')
            ->count();

        $monthCounts = DB::table('investigation_payments')
            ->selectRaw("CASE WHEN DueAmount <= 0 THEN 'paid' WHEN PaidAmount > 0 THEN 'partial' ELSE 'due' END AS st, COUNT(*) AS c")
            ->whereMonth('PaymentDate', now()->month)
            ->whereYear('PaymentDate', now()->year)
            ->groupBy('st')
            ->pluck('c', 'st');

        // ===== Chart: Patients & Revenue last 7 days =====
        $last7days = DB::table('investigation_payments')
            ->selectRaw('DATE(PaymentDate) AS date, COUNT(DISTINCT PatientId) AS total')
            ->whereDate('PaymentDate', '>=', now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $last7daysRevenue = DB::table('investigation_payments')
            ->selectRaw('DATE(PaymentDate) AS date, SUM(PaidAmount) AS total')
            ->whereDate('PaymentDate', '>=', now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartDates = collect();
        $chartPatients = collect();
        $chartRevenue = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $chartDates->push($date->format('d M'));
            $chartPatients->push((int) ($last7days[$date->format('Y-m-d')]->total ?? 0));
            $chartRevenue->push(round((float) ($last7daysRevenue[$date->format('Y-m-d')]->total ?? 0)));
        }

        // ===== Chart: Monthly revenue (this year) =====
        $monthlyRevenue = DB::table('investigation_payments')
            ->selectRaw('MONTH(PaymentDate) AS m, YEAR(PaymentDate) AS y, SUM(PaidAmount) AS total')
            ->whereYear('PaymentDate', now()->year)
            ->groupBy('m', 'y')
            ->orderBy('m')
            ->get()
            ->keyBy('m');

        $chartMonths = collect(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']);
        $chartMonthly = collect(range(1, 12))->map(fn ($m) => round((float) ($monthlyRevenue[$m]->total ?? 0)));

        // ===== Chart: Payment methods (this month) =====
        $methodStats = DB::table('investigation_payments')
            ->select('PaymentMethod', DB::raw('COUNT(*) AS count'), DB::raw('SUM(PaidAmount) AS total'))
            ->whereMonth('PaymentDate', now()->month)
            ->whereYear('PaymentDate', now()->year)
            ->whereNotNull('PaymentMethod')
            ->where('PaymentMethod', '!=', '')
            ->groupBy('PaymentMethod')
            ->orderByDesc('count')
            ->get();

        // ===== Lists =====
        $recentPayments = DB::table('investigation_payments')
            ->select('ID', 'BillNo', 'PatientName', 'PatientCode', 'TotalBill', 'Discount', 'PaidAmount', 'DueAmount', 'PaymentDate', 'Status', 'approval_status')
            ->orderByDesc('ID')
            ->limit(8)
            ->get();

        $topDue = DB::table('investigation_payments')
            ->select('BillNo', 'PatientName', 'PatientCode', 'MobileNo', 'DueAmount')
            ->where('DueAmount', '>', 0)
            ->where('approval_status', '!=', 'rejected')
            ->orderByDesc('DueAmount')
            ->limit(5)
            ->get();

        $doctors = DB::table('doctors as d')
            ->leftJoin('configspeciality as s', 's.id', '=', 'd.speciality')
            ->select('d.id', 'd.doctor_name', 's.name as speciality_name', 'd.contact', 'd.active')
            ->orderByDesc('d.active')
            ->orderBy('d.doctor_name')
            ->limit(10)
            ->get();

        $todayPatientRows = DB::table('investigation_payments')
            ->select('PatientName as patientname', 'PatientCode as patientcode', 'MobileNo as mobile_no', 'PaymentDate as created_at')
            ->whereDate('PaymentDate', $today)
            ->orderByDesc('PaymentDate')
            ->orderByDesc('ID')
            ->limit(8)
            ->get();

        $admissions = DB::table('patient_admission as pa')
            ->leftJoin('patients as p', 'p.patientcode', '=', 'pa.patientcode')
            ->leftJoin('beds as b', 'b.current_admission_id', '=', 'pa.id')
            ->select(
                'pa.id',
                'pa.patientcode',
                'p.patientname as patientname',
                'b.bed_number as bed_no',
                'pa.doctor_name',
                'pa.department',
                'pa.status',
                'pa.created_at'
            )
            ->orderByDesc('pa.id')
            ->limit(8)
            ->get();

        // ===== USG Follow-up reminders =====
        $followupOverdueCount = DB::table('usg_followups')
            ->where('status', 'pending')
            ->whereDate('followup_date', '<', $today)
            ->count();

        $followupTodayCount = DB::table('usg_followups')
            ->where('status', 'pending')
            ->whereDate('followup_date', $today)
            ->count();

        $followupReminders = DB::table('usg_followups as uf')
            ->leftJoin('patients as p', 'p.id', '=', 'uf.patient_id')
            ->leftJoin('doctors as d', 'd.id', '=', 'uf.doctor_id')
            ->select(
                'uf.*',
                'p.patientname',
                'p.patientcode',
                'p.mobile_no',
                'd.doctor_name'
            )
            ->where('uf.status', 'pending')
            ->whereDate('uf.followup_date', '<=', $today)
            ->orderBy('uf.followup_date', 'asc')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'totalPatients', 'todayPatients', 'todayRegistered', 'totalDoctors', 'activeDoctors',
            'todayAppointments', 'pendingAppointments', 'todayRevenue', 'yesterdayRevenue',
            'monthRevenue', 'totalRevenue', 'totalDue', 'pendingApprovals', 'monthCounts',
            'todayPrescription', 'todayVitals', 'followupToday',
            'chartDates', 'chartPatients', 'chartRevenue', 'chartMonths', 'chartMonthly', 'methodStats',
            'recentPayments', 'topDue', 'doctors', 'todayPatientRows', 'admissions',
            'followupOverdueCount', 'followupTodayCount', 'followupReminders'
        ));
    }
}
