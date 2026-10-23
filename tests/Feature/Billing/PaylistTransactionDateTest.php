<?php

namespace Tests\Feature\Billing;

use App\Http\Controllers\Billing\PaylistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase as ApplicationTestCase;

class PaylistTransactionDateTest extends ApplicationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
            $table->timestamps();
        });

        Schema::create('investigation_payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('type', 20);
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->timestamps();
        });

        DB::table('investigation_payments')->insert([
            'ID' => 1,
            'BillNo' => 'INV-1',
            'PatientId' => 10,
            'PatientCode' => 'P-10',
            'PatientName' => 'Test Patient',
            'TotalBill' => 155,
            'Discount' => 5,
            'PaidAmount' => 150,
            'DueAmount' => 0,
            'PaymentDate' => '2026-09-08',
        ]);

        DB::table('investigation_payment_transactions')->insert([
            ['invoice_id' => 1, 'type' => 'cash', 'amount' => 8.33, 'transaction_date' => '2026-09-01'],
            ['invoice_id' => 1, 'type' => 'cash', 'amount' => 12.50, 'transaction_date' => '2026-09-01'],
            ['invoice_id' => 1, 'type' => 'cash', 'amount' => 79.17, 'transaction_date' => '2026-09-01'],
            ['invoice_id' => 1, 'type' => 'discount', 'amount' => 3, 'transaction_date' => '2026-09-01'],
            ['invoice_id' => 1, 'type' => 'discount', 'amount' => 2, 'transaction_date' => '2026-09-01'],
            ['invoice_id' => 1, 'type' => 'cash', 'amount' => 50, 'transaction_date' => '2026-09-08'],
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('investigation_payment_transactions');
        Schema::dropIfExists('investigation_payments');

        parent::tearDown();
    }

    public function test_paylist_uses_transaction_date_and_separates_discount(): void
    {
        $controller = app(PaylistController::class);
        $request = Request::create('/Billing/Paylist/ajax/summary', 'GET', [
            'date_mode' => 'single',
            'single_date' => '2026-09-01',
        ]);

        $summary = $controller->summary($request)->getData(true);
        $list = $controller->list($request)->getData(true);

        $this->assertEquals(100.0, $summary['total_paid']);
        $this->assertEquals(5.0, $summary['total_discount']);
        $this->assertSame(1, $summary['total_invoices']);
        $this->assertCount(1, $list['flat']);
        $this->assertSame('2026-09-01', substr($list['flat'][0]['PaymentDate'], 0, 10));
        $this->assertSame('1:2026-09-01', $list['flat'][0]['GroupKey']);
        $this->assertSame(100.0, (float) $list['flat'][0]['PaidAmount']);
        $this->assertSame(5.0, (float) $list['flat'][0]['DiscountAmount']);
        $this->assertSame(105.0, (float) $list['flat'][0]['TransactionAmount']);
        $this->assertNotEmpty($list['flat'][0]['TransactionIds']);

        $allDatesRequest = Request::create('/Billing/Paylist/ajax/list', 'GET', [
            'date_mode' => 'all',
        ]);
        $allDatesList = $controller->list($allDatesRequest)->getData(true);

        $this->assertCount(2, $allDatesList['flat']);
        $this->assertSame(
            ['1:2026-09-01', '1:2026-09-08'],
            collect($allDatesList['flat'])->pluck('GroupKey')->sort()->values()->all()
        );
        $this->assertSame(50.0, (float) $allDatesList['flat'][0]['PaidAmount']);
    }
}
