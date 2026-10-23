<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Billing\PaymentController;
use App\Http\Controllers\Billing\DischargeBillPaymentController;
use App\Http\Controllers\Billing\CreateInvoiceController;
use App\Http\Controllers\Billing\DuelistFinalController;
use App\Http\Controllers\Billing\PaynowController;
use App\Http\Controllers\Billing\PaylistController;
use App\Http\Controllers\Billing\PaylistV2Controller;
use App\Http\Controllers\Billing\InvestigationReportController;
use App\Http\Controllers\Billing\AdminPaymentController;
use App\Http\Controllers\Billing\DueAdjustmentController;
use App\Http\Controllers\Billing\ManagerCollectionController;
use App\Http\Controllers\Billing\ReceiptCollectionController;

Route::middleware(['auth'])->prefix('Billing')->name('billing.')->group(function () {

    // Create Invoice
    Route::get('CreateInvoice',                        [CreateInvoiceController::class, 'index'])->name('invoice.index');
    Route::post('CreateInvoice/store',                 [CreateInvoiceController::class, 'store'])->name('invoice.store');

    Route::get('CreateInvoice/ajax/list',              [CreateInvoiceController::class, 'list'])->name('invoice.list');
    Route::get('CreateInvoice/ajax/bill-types',        [CreateInvoiceController::class, 'getBillTypes'])->name('invoice.getBillTypes');
    Route::get('CreateInvoice/ajax/chamber-types',     [CreateInvoiceController::class, 'getChamberTypes'])->name('invoice.getChamberTypes');
    Route::get('CreateInvoice/ajax/search-patient',    [CreateInvoiceController::class, 'searchPatient'])->name('invoice.searchPatient');
    Route::get('CreateInvoice/ajax/main-categories',   [CreateInvoiceController::class, 'getMain'])->name('invoice.getMain');
    Route::get('CreateInvoice/ajax/sub-categories',    [CreateInvoiceController::class, 'getSub'])->name('invoice.getSub');
    Route::get('CreateInvoice/ajax/get-doctors',       [CreateInvoiceController::class, 'getDoctors'])->name('invoice.getDoctors');
    Route::get('CreateInvoice/ajax/get-tmp',           [CreateInvoiceController::class, 'getTmp'])->name('invoice.getTmp');
    Route::post('CreateInvoice/ajax/add-tmp',          [CreateInvoiceController::class, 'addTmp'])->name('invoice.addTmp');
    Route::delete('CreateInvoice/ajax/remove-tmp',     [CreateInvoiceController::class, 'removeTmp'])->name('invoice.removeTmp');
    Route::post('CreateInvoice/ajax/clear-tmp',        [CreateInvoiceController::class, 'clearTmp'])->name('invoice.clearTmp');
    Route::get('CreateInvoice/ajax/get-collectors',    [CreateInvoiceController::class, 'getCollectors'])->name('invoice.getCollectors');

    Route::post('check-consultation-fee',               [CreateInvoiceController::class, 'checkConsultationFee'])->name('check.consultation.fee');

    Route::get('CreateInvoice/{id}/print',             [CreateInvoiceController::class, 'printInvoice'])->name('invoice.print');
    Route::get('CreateInvoice/{id}/pdf',               [CreateInvoiceController::class, 'downloadPdf'])->name('invoice.pdf');
    Route::get('CreateInvoice/{patientId}',            [CreateInvoiceController::class, 'show'])->name('invoice.show');

    // Manager Collection — reuses the Create Payment flow/view; only the
    // saved payment_source differs ('manager', forced server-side).
    // Helper AJAX endpoints are reused from CreateInvoice/ajax/* (not cloned).
    Route::get('ManagerCollection',                   [ManagerCollectionController::class, 'index'])->name('manager.index')->can('manager-collection');
    Route::post('ManagerCollection/store',            [ManagerCollectionController::class, 'store'])->name('manager.store')->can('manager-collection');

    // Reception Collection — reuses the Create Payment flow/view; only the
    // saved payment_source differs ('reception', forced server-side).
    // Helper AJAX endpoints are reused from CreateInvoice/ajax/* (not cloned).
    Route::get('ReceiptCollection',                   [ReceiptCollectionController::class, 'index'])->name('receipt.index')->can('receipt-collection');
    Route::post('ReceiptCollection/store',            [ReceiptCollectionController::class, 'store'])->name('receipt.store')->can('receipt-collection');

    // Payment
    Route::get('payment',                              [PaymentController::class, 'index'])->name('payment.index');
    Route::post('payment/store',                       [PaymentController::class, 'store'])->name('payment.store');
    Route::get('payment/{id}/print',                   [PaymentController::class, 'printInvoice'])->name('payment.print');
    Route::get('payment/{patientId}',                  [PaymentController::class, 'show'])->name('payment.show');

    // Pay Now
    Route::get('Paynow',                               [PaynowController::class, 'index'])->name('paynow.index');
    Route::get('Paynow/ajax/list',                     [PaynowController::class, 'list'])->name('paynow.list');
    Route::get('Paynow/ajax/search-patient',           [PaynowController::class, 'searchPatient'])->name('paynow.searchPatient');
    Route::get('Paynow/ajax/due-invoices/{patientId}', [PaynowController::class, 'getDueInvoices'])->name('paynow.getDueInvoices');
    Route::post('Paynow/store',                        [PaynowController::class, 'store'])->name('paynow.store');
    Route::post('Paynow/store-bulk',                   [PaynowController::class, 'storeBulk'])->name('paynow.store-bulk');

    // Discount Adjustment (Pay Now থেকেই — Due-তে সরাসরি discount দিয়ে কমানোর জন্য)
    Route::post('Paynow/adjust-discount',              [PaynowController::class, 'adjustDiscount'])->name('paynow.adjustDiscount');
    Route::get('Paynow/discount-history/{invoiceId}',  [PaynowController::class, 'discountHistory'])->name('paynow.discountHistory');
    Route::get('Paynow/references',                    [PaynowController::class, 'references'])->name('paynow.references');

    Route::get('Paynow/{id}/print',                    [PaynowController::class, 'print'])->name('paynow.print');
    Route::get('Paynow/{patientId}',                   [PaynowController::class, 'show'])->name('paynow.show');

    // Discharge Bill Payment
    Route::get('DischargeBillPayment',                                 [DischargeBillPaymentController::class, 'index'])->name('discharge.index');
    Route::post('DischargeBillPayment/store',                          [DischargeBillPaymentController::class, 'store'])->name('discharge.store');
    Route::get('DischargeBillPayment/detail/{id}',                     [DischargeBillPaymentController::class, 'detail'])->name('discharge.detail');
    Route::get('DischargeBillPayment/patient-payments/{patientId}',    [DischargeBillPaymentController::class, 'patientPayments'])->name('discharge.patientPayments');
    Route::get('DischargeBillPayment/by-admission/{admissionId}',      [DischargeBillPaymentController::class, 'byAdmission'])->name('discharge.byAdmission');
    Route::delete('DischargeBillPayment/delete/{id}',                  [DischargeBillPaymentController::class, 'destroy'])->name('discharge.destroy');
    Route::get('DischargeBillPayment/patient-data/{patientId}',        [DischargeBillPaymentController::class, 'getPatientData'])->name('discharge.patientData');
    Route::get('DischargeBillPayment/investigations/{patientId}',      [DischargeBillPaymentController::class, 'getInvestigations'])->name('discharge.investigations');
    Route::get('DischargeBillPayment/payment-history/{patientId}',     [DischargeBillPaymentController::class, 'getPaymentHistory'])->name('discharge.paymentHistory');
    Route::post('DischargeBillPayment/record-payment',                 [DischargeBillPaymentController::class, 'recordPayment'])->name('discharge.recordPayment');
    Route::get('DischargeBillPayment/get-investigations',              [DischargeBillPaymentController::class, 'getInvestigationsFromDatabase'])->name('discharge.getInvestigations');

    // Due Adjustment (concession against outstanding due — audit trail)
    Route::get('DueAdjustment',                              [DueAdjustmentController::class, 'index'])->name('dueadjustment.index');
    Route::post('DueAdjustment/store',                       [DueAdjustmentController::class, 'store'])->name('dueadjustment.store');
    Route::post('DueAdjustment/approve/{id}',                [DueAdjustmentController::class, 'approve'])->name('dueadjustment.approve');
    Route::post('DueAdjustment/reject/{id}',                 [DueAdjustmentController::class, 'reject'])->name('dueadjustment.reject');
    Route::post('DueAdjustment/reverse/{id}',                [DueAdjustmentController::class, 'reverse'])->name('dueadjustment.reverse');

    // Due List Final
    Route::get('DuelistFinal',                               [DuelistFinalController::class, 'index'])->name('duelist.index');
    Route::get('DuelistFinal/ajax/list',                     [DuelistFinalController::class, 'list'])->name('duelist.list');
    Route::get('DuelistFinal/ajax/summary',                  [DuelistFinalController::class, 'summary'])->name('duelist.summary');
    Route::get('DuelistFinal/patient-details/{patientId}',   [DuelistFinalController::class, 'patientDetails'])->name('duelist.patientDetails')
                                                             ->where('patientId', '[0-9]+');

    // Pay List
    Route::get('Paylist',                              [PaylistController::class, 'index'])->name('paylist.index');
    Route::get('Paylist/ajax/list',                    [PaylistController::class, 'list'])->name('paylist.list');
    Route::get('Paylist/ajax/summary',                 [PaylistController::class, 'summary'])->name('paylist.summary');
    Route::get('Paylist/ajax/search',                  [PaylistController::class, 'search'])->name('paylist.search');
    Route::post('Paylist/ajax/delete',                 [PaylistController::class, 'destroy'])->name('paylist.destroy');
    Route::get('Paylist/v2',                           [PaylistV2Controller::class, 'index'])->name('paylist.v2');

    // Investigation Revenue & Patient Report
    Route::get('InvestigationReport',                  [InvestigationReportController::class, 'index'])->name('investigation-report.index');
    Route::get('transaction',                         [InvestigationReportController::class, 'transactionHistory'])->name('investigation-report.history');
    Route::get('InvestigationTransactionHistory',     [InvestigationReportController::class, 'transactionHistory']);

    // Admin Payment Approval (Investigation Payments — Reference/Below-50% Pending)
    Route::get('approved',                             [AdminPaymentController::class, 'pendingPayments'])->name('admin.pending');
    Route::post('AdminApprovePayment/{id}',            [AdminPaymentController::class, 'approvePayment'])->name('admin.approve');
    Route::post('AdminRejectPayment/{id}',             [AdminPaymentController::class, 'rejectPayment'])->name('admin.reject');
    Route::get('report',                                [AdminPaymentController::class, 'auditReport'])->name('admin.audit');
    Route::get('AdminAuditReport',                      [AdminPaymentController::class, 'auditReport'])->name('admin.audit');

});
