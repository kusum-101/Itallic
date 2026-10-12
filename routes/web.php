<?php

use App\Http\Controllers\AccountMaster\GroupController;
use App\Http\Controllers\AccountMaster\LedgerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CredentialController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\Voucher\VoucherController;



// frontent
Route::view('/', 'home')->name('home');
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

//vouchers
// The same set of routes for each type in VoucherController::TYPES:
// /journal + /journal-register, /sales + /sales-register, /purchase, /receipt, /payment, /contra.
// defaults('type', ...) tells the controller which voucher type the page is for.
foreach (array_keys(VoucherController::TYPES) as $type) {
    Route::controller(VoucherController::class)->group(function () use ($type) {
        Route::get("/$type", 'create')->name($type)->defaults('type', $type);
        Route::post("/$type", 'store')->name("$type.store")->defaults('type', $type);
        Route::get("/$type-register", 'index')->name("$type-register")->defaults('type', $type);
        Route::get("/$type-register/export", 'export')->name("$type-register.export")->defaults('type', $type);
        Route::get("/$type/{id}", 'show')->whereNumber('id')->name("$type.show")->defaults('type', $type);
        Route::get("/$type/{id}/edit", 'edit')->whereNumber('id')->name("$type.edit")->defaults('type', $type);
        Route::put("/$type/{id}", 'update')->whereNumber('id')->name("$type.update")->defaults('type', $type);
        Route::delete("/$type/{id}", 'destroy')->whereNumber('id')->name("$type.destroy")->defaults('type', $type);
        Route::post("/$type/{id}/verify", 'verify')->whereNumber('id')->name("$type.verify")->defaults('type', $type);
    });
}

// A voucher's document proof (kept on the private disk, so it is opened through the controller)
Route::get('/voucher-document/{id}', [VoucherController::class, 'document'])->whereNumber('id')->name('voucher-document');

// District / City dropdowns of the ledger address fields (filled after picking a State / District)
Route::get('/districts/{stateId}', [LocationController::class, 'districts'])->whereNumber('stateId')->name('districts');
Route::get('/cities/{districtId}', [LocationController::class, 'cities'])->whereNumber('districtId')->name('cities');


// sidebar & header pages (placeholder until each page is built)
foreach ([
    // masters
    'alter', 'shipping-address', 'billing-address',
    'stock-group', 'stock-category', 'item', 'unit', 'godown', 'copy-allocation-details',
    'users', 'department', 'position',
    // vouchers
    'credit-note-register', 'cheque-register',
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

