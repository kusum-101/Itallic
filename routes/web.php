<?php

use App\Http\Controllers\AccountMaster\GroupController;
use App\Http\Controllers\AccountMaster\LedgerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterAdminController;
use App\Http\Controllers\CredentialController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\CompanyController;

//admin page
Route::view('/', 'admin-management.master-admin-login');
Route::post('/login-admin', [MasterAdminController::class, 'login']);
Route::get('/dashboard', [MasterAdminController::class, 'showDashboard'])->name('dashboard');
Route::get('/logout', [MasterAdminController::class, 'logout'])->name('logout');




// frontent
Route::view('/register', 'credential.user-register');
Route::post('/register',[CredentialController::class, 'register'] );
Route::get('/company', [CredentialController::class, 'company'])->name('company');
Route::post('/company/register', [CredentialController::class, 'registerCompany']);
Route::view('/login', 'credential.user-login')->name('login');
Route::post('/login', [CredentialController::class, 'login']);
Route::get('/dashboard-user', [CredentialController::class, 'dashboardUser'])->name('dashboardUser');
Route::post('/logout', [CredentialController::class, 'logout'])->name('logout');


//company
Route::view('/company/add-company', 'company.add-company')->name('add-company');
Route::get('/company-setting', [CompanyController::class, 'index'])->name('company-setting');

// select company (multi-company)
Route::get('/select-company', [CompanyController::class, 'select'])->name('select-company');
Route::post('/select-company/{id}', [CompanyController::class, 'choose'])->name('choose-company');


//group
Route::get('/group', [GroupController::class, 'create'])->name('group');
Route::post('/group', [GroupController::class, 'store'])->name('group.store');
Route::get('/list-group', [GroupController::class, 'index'])->name('list-group');
Route::get('/list-group/export', [GroupController::class, 'export'])->name('list-group.export');
Route::get('/group/{id}/edit', [GroupController::class, 'edit'])->name('group.edit');
Route::put('/group/{id}', [GroupController::class, 'update'])->name('group.update');
Route::delete('/group/{id}', [GroupController::class, 'destroy'])->name('group.destroy');

//ledger
Route::get('/ledger', [LedgerController::class, 'create'])->name('ledger');
Route::post('/ledger', [LedgerController::class, 'store'])->name('ledger.store');
Route::get('/list-ledger', [LedgerController::class, 'index'])->name('list-ledger');
Route::get('/ledger/{id}/edit', [LedgerController::class, 'edit'])->name('ledger.edit');
Route::put('/ledger/{id}', [LedgerController::class, 'update'])->name('ledger.update');
Route::delete('/ledger/{id}', [LedgerController::class, 'destroy'])->name('ledger.destroy');


// sidebar & header pages (placeholder until each page is built)
foreach ([
    // masters
    'alter', 'shipping-address', 'billing-address',
    'stock-group', 'stock-category', 'item', 'unit', 'godown', 'copy-allocation-details',
    'users', 'department', 'position',
    // vouchers
    'payment-register', 'purchase-register', 'contra-register', 'receipt-register',
    'journal-register', 'sales-register', 'credit-note-register', 'cheque-register',
    'material-inward', 'material-outward', 'material-internal-transfer',
    'purchase-order', 'rate-card', 'quotation',
    // reports
    'day-book', 'balance-sheet', 'group-report', 'profit-loss', 'ledger-summary',
    'stock-summary', 'all-vouchers', 'gst-sale', 'payment-due', 'leave-summary',
    // hr
    'daily-reports', 'attendance', 'leave',
    // account
    'profile', 'settings', 'notifications',
] as $page) {
    Route::get("/$page", PageController::class)->name($page);
}
