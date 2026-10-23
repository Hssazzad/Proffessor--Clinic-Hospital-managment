<?php

namespace Tests\Feature;

use App\Http\Controllers\Billing\PaylistController;
use App\Http\Controllers\DashboardController;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase as ApplicationTestCase;

class DashboardRevenueDateTest extends ApplicationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() === 'sqlite') {
            $pdo = DB::connection()->getPdo();
            $pdo->sqliteCreateFunction('MONTH', function ($date) {
                return $date ? (int) date('m', strtotime($date)) : null;
            });
            $pdo->sqliteCreateFunction('YEAR', function ($date) {
                return $date ? (int) date('Y', strtotime($date)) : null;
            });
        }

        Schema::create('investigation_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('ID')->primary();
            $table->string('BillNo')->nullable();
            $table->unsignedBigInteger('PatientId')->nullable();
            $table->string('PatientCode')->nullable();
            $table->string('PatientName')->nullable();
            $table->string('MobileNo')->nullable();
            $table->decimal('TotalBill', 12, 2)->default(0);
            $table->decimal('Discount', 12, 2)->default(0);
            $table->decimal('PaidAmount', 12, 2)->default(0);
            $table->decimal('DueAmount', 12, 2)->default(0);
            $table->date('PaymentDate')->nullable();
            $table->string('PaymentMethod', 50)->nullable();
            $table->string('Status', 50)->nullable();
            $table->string('approval_status', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('investigation_payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('type', 20);
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->string('payment_method', 50)->nullable();
            $table->string('collected_by', 100)->nullable();
            $table->string('source', 50)->nullable();
            $table->timestamps();
        });

        // Other tables required by DashboardController::index
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patientcode')->nullable();
            $table->string('patientname')->nullable();
            $table->string('mobile_no')->nullable();
            $table->timestamps();
        });

        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('doctor_name')->nullable();
            $table->unsignedBigInteger('speciality')->nullable();
            $table->string('contact')->nullable();
            $table->tinyInteger('active')->default(1);
            $table->timestamps();
        });

        Schema::create('configspeciality', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->date('appointment_date')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->date('next_appointment')->nullable();
            $table->timestamps();
        });

        Schema::create('preconassessment', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('patient_admission', function (Blueprint $table) {
            $table->id();
            $table->string('patientcode')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('department')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('current_admission_id')->nullable();
            $table->string('bed_number')->nullable();
            $table->timestamps();
        });

        Schema::create('usg_followups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->date('followup_date')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('usg_followups');
        Schema::dropIfExists('beds');
        Schema::dropIfExists('patient_admission');
        Schema::dropIfExists('preconassessment');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('configspeciality');
        Schema::dropIfExists('doctors');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('investigation_payment_transactions');
        Schema::dropIfExists('investigation_payments');

        parent::tearDown();
    }

    public function test_dashboard_today_and_yesterday_revenue_match_paylist_summary(): void
    {
        $todayDate = '2026-09-09';
        $yesterdayDate = '2026-09-08';
        Carbon::setTestNow(Carbon::parse($todayDate . ' 12:00:00'));

        // Invoice 1: created yesterday, partially paid yesterday (৳500), completed today (৳700). Total bill: ৳1200.
        // PaymentDate in header was updated to today (2026-09-09) when fully paid.
        DB::table('investigation_payments')->insert([
            'ID' => 1,
            'BillNo' => 'INV-001',
            'PatientId' => 101,
            'PatientCode' => 'P-101',
            'PatientName' => 'Patient One',
            'TotalBill' => 1200,
            'Discount' => 0,
            'PaidAmount' => 1200, // Total paid on header is 1200
            'DueAmount' => 0,
            'PaymentDate' => $todayDate, // Header shows today's date
        ]);

        // Invoice 2: created today, ৳10,000 cash paid today, ৳70 discount today
        DB::table('investigation_payments')->insert([
            'ID' => 2,
            'BillNo' => 'INV-002',
            'PatientId' => 102,
            'PatientCode' => 'P-102',
            'PatientName' => 'Patient Two',
            'TotalBill' => 10070,
            'Discount' => 70,
            'PaidAmount' => 10000,
            'DueAmount' => 0,
            'PaymentDate' => $todayDate,
        ]);

        // Actual payment transactions:
        DB::table('investigation_payment_transactions')->insert([
            // Invoice 1 installment on yesterday:
            ['invoice_id' => 1, 'type' => 'cash', 'amount' => 500.00, 'transaction_date' => $yesterdayDate],
            // Invoice 1 installment on today:
            ['invoice_id' => 1, 'type' => 'cash', 'amount' => 700.00, 'transaction_date' => $todayDate],
            // Invoice 2 payments today:
            ['invoice_id' => 2, 'type' => 'cash', 'amount' => 10000.00, 'transaction_date' => $todayDate],
            ['invoice_id' => 2, 'type' => 'discount', 'amount' => 70.00, 'transaction_date' => $todayDate],
        ]);

        // Paylist summary for Today:
        // Cash paid today = 700 + 10000 = 10700. Discount = 70.
        $paylistController = app(PaylistController::class);
        $todayPaylistReq = Request::create('/Billing/Paylist/summary', 'POST', [
            'date_mode' => 'single',
            'single_date' => $todayDate,
        ]);
        $paylistTodaySummary = $paylistController->summary($todayPaylistReq)->getData(true);

        $this->assertEquals(10700.00, $paylistTodaySummary['total_paid']);
        $this->assertEquals(70.00, $paylistTodaySummary['total_discount']);

        // Paylist summary for Yesterday:
        // Cash paid yesterday = 500.
        $yesterdayPaylistReq = Request::create('/Billing/Paylist/summary', 'POST', [
            'date_mode' => 'single',
            'single_date' => $yesterdayDate,
        ]);
        $paylistYesterdaySummary = $paylistController->summary($yesterdayPaylistReq)->getData(true);
        $this->assertEquals(500.00, $paylistYesterdaySummary['total_paid']);

        // Now check DashboardController
        $dashboardController = app(DashboardController::class);
        $view = $dashboardController->index();
        $viewData = $view->getData();

        // Under old logic:
        // $todayRevenue would have been 1200 + 10000 = 11200 (including yesterday's 500 because PaymentDate is today!)
        // Under new transaction_date logic:
        // $todayRevenue must be exactly 10700.00!
        $this->assertEquals(10700.00, $viewData['todayRevenue']);
        $this->assertEquals($paylistTodaySummary['total_paid'], $viewData['todayRevenue']);

        // Yesterday revenue must be 500.00, matching Paylist yesterday total_paid!
        $this->assertEquals(500.00, $viewData['yesterdayRevenue']);
        $this->assertEquals($paylistYesterdaySummary['total_paid'], $viewData['yesterdayRevenue']);

        Carbon::setTestNow();
    }
}
