<?php

use App\Http\Controllers\Accounting\AccountController;
use App\Http\Controllers\Accounting\GeneralLedgerController;
use App\Http\Controllers\Accounting\GeneralLedgerMockupController;
use App\Http\Controllers\Accounting\JournalController;
use App\Http\Controllers\Accounting\JournalMockupController;
use App\Http\Controllers\Accounting\JournalTemplateController;
use App\Http\Controllers\Accounting\TrialBalanceController;
use App\Http\Controllers\Accounting\TrialBalanceMockupController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Cash\CashPositionController;
use App\Http\Controllers\Cash\CashSessionController;
use App\Http\Controllers\Cash\CashTransferController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\Member\MemberController;
use App\Http\Controllers\Organization\AuthorityLimitController;
use App\Http\Controllers\Organization\OfficeController;
use App\Http\Controllers\Organization\RoleController;
use App\Http\Controllers\Organization\UserController;
use App\Http\Controllers\Savings\MemberActivationController;
use App\Http\Controllers\Savings\SavingsAccountController;
use App\Http\Controllers\Savings\SavingsProductController;
use App\Http\Controllers\Savings\SavingsTransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'login'])->name('login');
    Route::post('/signin', [AuthController::class, 'signin'])
        ->middleware('throttle:5,1')
        ->name('signin');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // DEMO
    Route::post('/demo/switch-user', [DemoController::class, 'switchUser'])->name('switchUser');

    // OFFICE
    Route::get('/office', [OfficeController::class, 'index'])->middleware('module.access:organization,view')->name('office');
    Route::post('/post-office', [OfficeController::class, 'store'])->middleware('module.access:organization,manage')->name('postOffice');
    Route::put('/office/{id}/update', [OfficeController::class, 'update'])->middleware('module.access:organization,manage')->name('updateOffice');

    // USER
    Route::get('/user', [UserController::class, 'index'])->middleware('module.access:organization,view')->name('user');
    Route::post('/post-user', [UserController::class, 'store'])->middleware('module.access:organization,manage')->name('postUser');
    Route::put('/user/{id}/update', [UserController::class, 'update'])->middleware('module.access:organization,manage')->name('updateUser');

    // ROLE
    Route::get('/role', [RoleController::class, 'index'])->middleware('module.access:organization,view')->name('role');

    // AUTHORITY LIMIT
    Route::get('/authority-limit', [AuthorityLimitController::class, 'index'])->middleware('module.access:organization,view')->name('authorityLimit');

    // MEMBER
    Route::get('/member', [MemberController::class, 'index'])->middleware('module.access:member,view')->name('member');
    Route::get('/member/create', [MemberController::class, 'create'])->middleware('module.access:member,operate')->name('createMember');
    Route::post('/post-member', [MemberController::class, 'store'])->middleware('module.access:member,operate')->name('postMember');
    Route::post('/member/check-nik', [MemberController::class, 'checkNik'])->middleware('module.access:member,operate')->name('checkMemberNik');
    Route::get('/member/{id}/show', [MemberController::class, 'show'])->middleware('module.access:member,view')->name('detailMember');
    Route::post('/member/{id}/approve', [MemberController::class, 'approve'])->middleware('module.access:member,approve')->name('approveMember');
    Route::post('/member/{id}/reject', [MemberController::class, 'reject'])->middleware('module.access:member,approve')->name('rejectMember');

    // SAVINGS PRODUCT
    Route::get('/savings-product', [SavingsProductController::class, 'index'])->middleware('module.access:savings,view')->name('savingsProduct');

    // SAVINGS ACCOUNT
    Route::get('/savings-account', [SavingsAccountController::class, 'index'])->middleware('module.access:savings,view')->name('savingsAccount');
    Route::get('/savings-account/{id}/show', [SavingsAccountController::class, 'show'])->middleware('module.access:savings,view')->name('detailSavingsAccount');
    Route::get('/savings-account/{id}/print', [SavingsAccountController::class, 'print'])->middleware('module.access:savings,view')->name('printSavingsAccount');

    // SAVINGS TRANSACTION
    Route::get('/savings-transaction', [SavingsTransactionController::class, 'index'])->middleware('module.access:savings,view')->name('savingsTransaction');
    Route::get('/savings-transaction/create', [SavingsTransactionController::class, 'create'])->middleware('module.access:savings,operate')->name('createSavingsTransaction');
    Route::post('/savings-transaction/search-account', [SavingsTransactionController::class, 'searchAccount'])->middleware('module.access:savings,operate')->name('searchSavingsAccount');
    Route::post('/post-savings-transaction', [SavingsTransactionController::class, 'store'])->middleware('module.access:savings,operate')->name('postSavingsTransaction');
    Route::get('/savings-transaction/{id}/show', [SavingsTransactionController::class, 'show'])->middleware('module.access:savings,view')->name('detailSavingsTransaction');
    Route::get('/savings-transaction/{id}/print', [SavingsTransactionController::class, 'print'])->middleware('module.access:savings,view')->name('printSavingsTransaction');
    Route::post('/savings-transaction/{id}/approve', [SavingsTransactionController::class, 'approve'])->middleware('module.access:savings,approve')->name('approveSavingsTransaction');
    Route::post('/savings-transaction/{id}/reject', [SavingsTransactionController::class, 'reject'])->middleware('module.access:savings,approve')->name('rejectSavingsTransaction');

    // MEMBER ACTIVATION
    Route::get('/member-activation', [MemberActivationController::class, 'index'])->middleware('module.access:savings,operate')->name('memberActivation');
    Route::post('/member-activation/{id}/activate', [MemberActivationController::class, 'store'])->middleware('module.access:savings,operate')->name('activateMember');

    // CASH SESSION
    Route::get('/cash-session', [CashSessionController::class, 'index'])->middleware('module.access:cash,view')->name('cashSession');
    Route::post('/post-cash-session', [CashSessionController::class, 'store'])->middleware('module.access:cash,operate')->name('postCashSession');

    // CASH TRANSFER
    Route::get('/cash-transfer', [CashTransferController::class, 'index'])->middleware('module.access:cash,view')->name('cashTransfer');
    Route::post('/post-cash-transfer', [CashTransferController::class, 'store'])->middleware('module.access:cash,operate')->name('postCashTransfer');
    Route::post('/cash-transfer/{id}/confirm', [CashTransferController::class, 'confirm'])->middleware('module.access:cash,view')->name('confirmCashTransfer');
    Route::post('/cash-transfer/{id}/reject', [CashTransferController::class, 'reject'])->middleware('module.access:cash,view')->name('rejectCashTransfer');

    // CASH POSITION
    Route::get('/cash-position', [CashPositionController::class, 'index'])->middleware('module.access:cash,view')->name('cashPosition');

    // JOURNAL MOCKUP
    Route::get('/journal-mockup', [JournalMockupController::class, 'index'])->middleware('module.access:accounting,view')->name('journalMockup');
    Route::get('/journal-mockup/{id}/show', [JournalMockupController::class, 'show'])->middleware('module.access:accounting,view')->name('detailJournalMockup');

    // GENERAL LEDGER MOCKUP
    Route::get('/general-ledger-mockup', [GeneralLedgerMockupController::class, 'index'])->middleware('module.access:accounting,view')->name('generalLedgerMockup');

    // TRIAL BALANCE MOCKUP
    Route::get('/trial-balance-mockup', [TrialBalanceMockupController::class, 'index'])->middleware('module.access:accounting,view')->name('trialBalanceMockup');

    // ACCOUNT
    Route::get('/account', [AccountController::class, 'index'])->middleware('module.access:accounting,view')->name('account');

    // JOURNAL TEMPLATE
    Route::get('/journal-template', [JournalTemplateController::class, 'index'])->middleware('module.access:accounting,view')->name('journalTemplate');

    // JOURNAL
    Route::get('/journal', [JournalController::class, 'index'])->middleware('module.access:accounting,view')->name('journal');
    Route::get('/journal/{id}/show', [JournalController::class, 'show'])->middleware('module.access:accounting,view')->name('detailJournal');
    Route::post('/journal/{id}/reverse', [JournalController::class, 'reverse'])->middleware('module.access:accounting,manage')->name('reverseJournal');

    // GENERAL LEDGER
    Route::get('/general-ledger', [GeneralLedgerController::class, 'index'])->middleware('module.access:accounting,view')->name('generalLedger');

    // TRIAL BALANCE
    Route::get('/trial-balance', [TrialBalanceController::class, 'index'])->middleware('module.access:accounting,view')->name('trialBalance');
});
