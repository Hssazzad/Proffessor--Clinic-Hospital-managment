<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\OtPaymentController;
use App\Http\Controllers\TransactionController;

Route::middleware(['auth'])->get('/GeneralLedger/ShowGeneralLedger', [AccountingController::class, 'showGeneralLedger'])
    ->name('accounting.showgeneralledger');

Route::middleware(['auth'])->get('/GeneralLedger/Incomestatement', [AccountingController::class, 'incomeStatement'])
    ->name('accounting.incomestatement');

// ---- Monthly Profit (monthly income-expense statement) ----
Route::middleware(['auth'])->get('/GeneralLedger/MonthlyReport', [AccountingController::class, 'monthlyProfit'])
    ->name('accounting.monthlyreport');

Route::middleware(['auth'])->get('/Accounting/InvestigationReport', [AccountingController::class, 'investigationReconciliation'])
    ->name('accounting.investigation.reconciliation');

Route::middleware(['auth'])->get('/GeneralLedger/ExpenditureStatement', [AccountingController::class, 'expenditureStatement'])
    ->name('accounting.expenditurestatement');

Route::middleware(['auth'])->get('/GeneralLedger/AllaccountBalance', [TransactionController::class, 'showAllAccountBalance'])
    ->name('accounting.allaccountbalance');
Route::middleware(['auth'])->get('/GeneralLedger/AllaccountBalanceData', [TransactionController::class, 'allAccountBalanceData'])
    ->name('accounting.allaccountbalancedata');
Route::middleware(['auth'])->get('/GeneralLedger/AllaccountBalancePDF', [TransactionController::class, 'allAccountBalancePDF'])
    ->name('accounting.allaccountbalancepdf');

Route::middleware(['auth'])->get('/GeneralLedger/BalanceSheet', [AccountingController::class, 'balanceSheet'])
    ->name('accounting.balancesheet');

Route::middleware(['auth'])->get('/Clossofbusiness/cobprocess', [AccountingController::class, 'cobProcess'])
    ->name('accounting.cobprocess');

Route::middleware(['auth'])->group(function () {
    Route::get('/GeneralLedger/SubGroupSummary', [AccountingController::class, 'subGroupSummary'])->name('accounting.subgroupsummary');
    Route::post('/GeneralLedger/ShowSubGroupSummary', [AccountingController::class, 'showSubGroupSummary'])->name('accounting.showsubgroupsummary');
    Route::post('/GeneralLedger/ShowGroupDetails', [AccountingController::class, 'showGroupDetails'])->name('accounting.showgroupdetails');
    Route::get('/GeneralLedger/NetPrifitLostsummary', [AccountingController::class, 'netProfitLoss'])->name('accounting.netprofitloss');
    Route::post('/GeneralLedger/updateBalances', [AccountingController::class, 'updateBalances'])->name('accounting.updatebalances');
});

/*
|--------------------------------------------------------------------------
| Accounting Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('Accounting')->group(function () {

    // ---- Add GL Group (Main Head) ----
    Route::get('addGroup', [AccountingController::class, 'addgroup'])
        ->name('accounting.addgroup');
    Route::post('addgroupaction', [AccountingController::class, 'addGroupAction'])
        ->name('accounting.addgroupaction');
    Route::post('getgrouplist', [AccountingController::class, 'getGroupList'])
        ->name('accounting.getgrouplist');
    Route::post('nextglgroup', [AccountingController::class, 'nextGlGroup'])
        ->name('accounting.nextglgroup');

    // ---- Add GL Sub Group (Sub Head) ----
    Route::get('addsubgroup', [AccountingController::class, 'addSubGroup'])
        ->name('accounting.addsubgroup');
    Route::post('addsubgroupaction', [AccountingController::class, 'addSubGroupAction'])
        ->name('accounting.addsubgroupaction');
    Route::post('genglsubgroupcode', [AccountingController::class, 'genGlSubGroupCode'])
        ->name('accounting.genglsubgroupcode');
    Route::post('nextglsubgroup', [AccountingController::class, 'nextGlSubGroup'])
        ->name('accounting.nextglsubgroup');
    Route::post('getsubgrouplist', [AccountingController::class, 'getSubGroupList'])
        ->name('accounting.getsubgrouplist');

    // ---- Add GL Account (4th Tier) ----
    Route::get('addAccount', [AccountingController::class, 'addAccount'])
        ->name('accounting.addaccount');
    Route::post('addaccountaction', [AccountingController::class, 'addAccountAction'])
        ->name('accounting.addaccountaction');
    Route::post('getSubGroupByGlGroup', [AccountingController::class, 'getSubGroupByGlGroup'])
        ->name('accounting.getsubgroupbyglgroup');
    Route::post('nextAccountNo', [AccountingController::class, 'nextAccountNo'])
        ->name('accounting.nextaccountno');
    Route::post('getAccountList', [AccountingController::class, 'getAccountList'])
        ->name('accounting.getaccountlist');
    Route::get('accountList', [AccountingController::class, 'accountList'])
        ->name('accounting.accountlist');
    Route::get('account/{account}/edit', [AccountingController::class, 'editAccount'])
        ->name('accounting.editaccount');
    Route::put('account/{account}', [AccountingController::class, 'updateAccount'])
        ->name('accounting.updateaccount');
    Route::post('deleteAccount', [AccountingController::class, 'deleteAccount'])
        ->name('accounting.deleteaccount');
    Route::get('subgroupList', [AccountingController::class, 'subgroupList'])
        ->name('accounting.subgrouplist');
    Route::get('subgroup/{subgroup}/edit', [AccountingController::class, 'editSubGroup'])
        ->name('accounting.editsubgroup');
    Route::put('subgroup/{subgroup}', [AccountingController::class, 'updateSubGroup'])
        ->name('accounting.updatesubgroup');
    Route::post('deleteSubGroup', [AccountingController::class, 'deleteSubGroup'])
        ->name('accounting.deletesubgroup');
    Route::get('AddInitialBalance', [AccountingController::class, 'initialBalance'])
        ->name('accounting.initialbalance');
    Route::post('AddInitialBalance', [AccountingController::class, 'initialBalanceAction'])
        ->name('accounting.initialbalanceaction');
    Route::get('changeAccountNo', [AccountingController::class, 'changeAccountNo'])
        ->name('accounting.changeaccount');
    Route::post('changeAccountNo', [AccountingController::class, 'changeAccountNoAction'])
        ->name('accounting.changeaccountaction');

    // ---- Shared AJAX endpoints ----
    Route::post('getGroupCodeByGlType', [AccountingController::class, 'getGroupCodeByGlType'])
        ->name('accounting.getGroupCodeByGlType');
    Route::get('group/{group}/edit', [AccountingController::class, 'editGroup'])
        ->name('accounting.editgroup');
    Route::put('group/{group}', [AccountingController::class, 'updateGroup'])
        ->name('accounting.updategroup');
    Route::post('deleteGroup', [AccountingController::class, 'deleteGroup'])
        ->name('accounting.deletegroup');
    Route::post('GetGroupCodeByglType', [AccountingController::class, 'getGroupCodeByGlType']);
    Route::post('GetgroupList', [AccountingController::class, 'getGroupList']);
    Route::post('GetSubGroupList', [AccountingController::class, 'getSubGroupList']);
    Route::post('GenGlSubgGoupCode', [AccountingController::class, 'genGlSubGroupCode']);
    Route::post('NextaccountNo', [AccountingController::class, 'nextAccountNo']);
    Route::post('GetAccountList', [AccountingController::class, 'getAccountList']);
    Route::post('GetSubGroupBygl_Group', [AccountingController::class, 'getSubGroupByGlGroup']);
    Route::post('NextaccountNoForGroup', [AccountingController::class, 'nextAccountNo']);
    Route::post('GetGLNextCodeByGroup', [AccountingController::class, 'nextGlSubGroup']);
    Route::post('NextGLAccountNo', [AccountingController::class, 'nextAccountNo']);
    Route::post('getgroupcodebygltype', [AccountingController::class, 'getGroupCodeByGlType'])
        ->name('accounting.getgroupcodebygltype');
    Route::post('GetGLCodeBYAccount', [AccountingController::class, 'getGlCodeByAccount'])
        ->name('accounting.getglcodebyaccount');
    Route::post('GetSubGLCodeBYGlCode', [AccountingController::class, 'getSubGlCodeByGlCode'])
        ->name('accounting.getsubglcodebyglcode');
});

/*
|--------------------------------------------------------------------------
| Transaction Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('Transaction')->group(function () {

    // ---- Cash Payment ----
    Route::get('CashPayment', [TransactionController::class, 'cashPayment'])
        ->name('transaction.cashpayment');
    Route::post('SaveCashPaymentTransaction', [TransactionController::class, 'cashPaymentAction'])
        ->name('transaction.cashpaymentaction');
    Route::post('cashpaymentaction', [TransactionController::class, 'cashPaymentAction']);

    // ---- Cash Receive ----
    Route::get('CashReceive', [TransactionController::class, 'cashReceive'])
        ->name('transaction.cashreceive');
    Route::post('SaveCashReceiveTransaction', [TransactionController::class, 'cashReceiveAction'])
        ->name('transaction.saveCashReceive');
    Route::post('cashreceiveaction', [TransactionController::class, 'cashReceiveAction'])
        ->name('transaction.cashreceiveaction');

    // ---- Multiple Cash Receive ----
    Route::get('multipleCashReceive', [TransactionController::class, 'multipleCashReceive'])
        ->name('transaction.multiplecashreceive');
    Route::post('multipleCashReceive', [TransactionController::class, 'multipleCashReceiveAction'])
        ->name('transaction.multiplecashreceiveaction');

    // ---- Multiple Cash Payment ----
    Route::get('MultipleCashPayment', [TransactionController::class, 'multipleCashPayment'])
        ->name('transaction.multiplecashpayment');
    Route::post('MultipleCashPayment', [TransactionController::class, 'multipleCashPaymentAction'])
        ->name('transaction.multiplecashpaymentaction');
    Route::get('multipleCashPayment', [TransactionController::class, 'multipleCashPayment']);
    Route::post('multipleCashPayment', [TransactionController::class, 'multipleCashPaymentAction']);

    // ---- Bank Receive ----
    Route::get('BankReceive', [TransactionController::class, 'bankReceive'])
        ->name('transaction.bankreceive');
    Route::get('bankreceive', [TransactionController::class, 'bankReceive']);
    Route::post('SaveBankReceiveTransaction', [TransactionController::class, 'bankReceiveAction'])
        ->name('transaction.saveBankReceive');
    Route::post('bankreceiveaction', [TransactionController::class, 'bankReceiveAction'])
        ->name('transaction.bankreceiveaction');

    // ---- Bank Payment ----
    Route::get('BankPayment', [TransactionController::class, 'bankPayment'])
        ->name('transaction.bankpayment');
    Route::get('bankpayment', [TransactionController::class, 'bankPayment']);
    Route::post('SaveBankPaymentTransaction', [TransactionController::class, 'bankPaymentAction'])
        ->name('transaction.saveBankPayment');
    Route::post('bankpaymentaction', [TransactionController::class, 'bankPaymentAction'])
        ->name('transaction.bankpaymentaction');

    // ---- OT Payment (payout to surgeon / assistant / anaesthetist / manager) ----
    Route::get('otpayment', [OtPaymentController::class, 'index'])
        ->name('transaction.otpayment');
    Route::post('SaveOtPayment', [OtPaymentController::class, 'store'])
        ->name('transaction.saveOtPayment');
    Route::post('otpaymentaction', [OtPaymentController::class, 'store']);
    Route::get('otpayment/payees', [OtPaymentController::class, 'payeeNames'])
        ->name('transaction.otpayment.payees');
    Route::get('otpayment/patients', [OtPaymentController::class, 'patients'])
        ->name('transaction.otpayment.patients');

    // ---- Transfer Posting ----
    Route::get('TransferPosting', [TransactionController::class, 'transferPosting'])
        ->name('transaction.transferposting');
    Route::post('SaveTranferPosting', [TransactionController::class, 'transferPostingAction'])
        ->name('transaction.transferpostingaction');
    Route::post('transferpostingaction', [TransactionController::class, 'transferPostingAction']);

    // ---- Show Ledger & Statements ----
    Route::get('Showledger', [TransactionController::class, 'showLedger'])
        ->name('transaction.showledger');
    Route::match(['get', 'post'], 'ShowLedgerStatement', [TransactionController::class, 'showLedgerStatement'])
        ->name('transaction.showledgerstatement');
    Route::match(['get', 'post'], 'ShowLedgerStatementPDF', [TransactionController::class, 'showLedgerStatementPDF'])
        ->name('transaction.showledgerstatementpdf');

    // ---- AJAX Balance Lookup ----
    Route::match(['get', 'post'], 'fetch_account_bal', [TransactionController::class, 'fetchAccountBal'])
        ->name('transaction.fetchAccountBal');

    // ---- Unauthorized Transactions & Authorization ----
    Route::get('UnauthorizeList', [TransactionController::class, 'unauthorizeList'])
        ->name('transaction.unauthorizelist');
    Route::get('UnauthorizeTransaction', [TransactionController::class, 'unauthorizeList']);
    Route::get('unauthorizelist', [TransactionController::class, 'unauthorizeList']);

    Route::get('AuthorizeReview', [TransactionController::class, 'authorizeReview'])
        ->name('transaction.authorizereview');
    Route::get('AuthorizeTransaction', [TransactionController::class, 'authorizeReview']);
    Route::get('authorizereview', [TransactionController::class, 'authorizeReview']);

    Route::post('authorize', [TransactionController::class, 'authorizeTransaction'])
        ->name('transaction.authorize');
    Route::post('unauthorize/delete', [TransactionController::class, 'deleteUnauthTransaction'])
        ->name('transaction.unauthorize.delete');
});

/*
|--------------------------------------------------------------------------
| Supplementary Reports
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('Suplimentary')->group(function () {
    Route::get('CashReceive', [TransactionController::class, 'supplementaryCashReceive'])
        ->name('supplementary.cashreceive');
    Route::get('CashReceive/download', [TransactionController::class, 'supplementaryCashReceivePdf'])
        ->name('supplementary.cashreceive.pdf');
    Route::get('CashPayment', [TransactionController::class, 'supplementaryCashPayment'])
        ->name('supplementary.cashpayment');
    Route::get('CashPayment/download', [TransactionController::class, 'supplementaryCashPaymentPdf'])
        ->name('supplementary.cashpayment.pdf');
    Route::get('TrailBalance', [TransactionController::class, 'supplementaryTrialBalance'])
        ->name('supplementary.trialbalance');
    Route::get('TrailBalance/download', [TransactionController::class, 'supplementaryTrialBalancePdf'])
        ->name('supplementary.trialbalance.pdf');
    Route::get('DailyProfitReport', [TransactionController::class, 'dailyProfitReport'])
        ->name('supplementary.dailyprofitreport');
});