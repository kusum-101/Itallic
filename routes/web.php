<?php

use App\Http\Controllers\AccountMaster\GroupController;
use App\Http\Controllers\AccountMaster\LedgerController;
use App\Http\Controllers\InventoryMaster\GodownController;
use App\Http\Controllers\InventoryMaster\ItemController;
use App\Http\Controllers\InventoryMaster\StockCategoryController;
use App\Http\Controllers\InventoryMaster\StockGroupController;
use App\Http\Controllers\InventoryMaster\UnitController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CredentialController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\Voucher\IncomingPurchaseController;
use App\Http\Controllers\Voucher\StockVoucherController;
use App\Http\Controllers\Voucher\VoucherController;



// frontent
Route::view('/', 'home')->name('home');
Route::view('/register', 'credential.user-register');
Route::post('/register',[CredentialController::class, 'register'] );
Route::get('/company', [CredentialController::class, 'company'])->name('company');
Route::post('/company/register', [CredentialController::class, 'registerCompany']);
Route::view('/login', 'credential.user-login')->name('login');
Route::post('/login', [CredentialController::class, 'login']);
Route::get('/dashboard-user', [DashboardController::class, 'index'])->name('dashboardUser');
Route::post('/logout', [CredentialController::class, 'logout'])->name('logout');


//company
Route::get('/company/add-company', [CompanyController::class, 'create'])->name('add-company');
Route::get('/company-setting', [CompanyController::class, 'index'])->name('company-setting');

// select company (multi-company)
Route::get('/select-company', [CompanyController::class, 'select'])->name('select-company');
Route::post('/select-company/{id}', [CompanyController::class, 'choose'])->name('choose-company');
Route::post('/financial-year', [CompanyController::class, 'chooseYear'])->name('choose-year');


//group
// Export comes before the resource, otherwise /groups/export would be taken as /groups/{id}
Route::get('/groups/export', [GroupController::class, 'export'])->name('groups.export');
Route::get('/chart-of-accounts', [GroupController::class, 'chart'])->name('chart-of-accounts');
// groups.index, groups.create, groups.store, groups.edit, groups.update, groups.destroy
// (no show page). parameters() keeps the URL parameter as {id}, so edit($id) etc. stay the same.
Route::resource('groups', GroupController::class)->except('show')->parameters(['groups' => 'id']);

//ledger
// ledgers.index, ledgers.create, ledgers.store, ledgers.edit, ledgers.update, ledgers.destroy
Route::resource('ledgers', LedgerController::class)->except('show')->parameters(['ledgers' => 'id']);

// Old group / ledger URLs (before the resource routes), so saved bookmarks still open the page
Route::permanentRedirect('/group', '/groups/create');
Route::permanentRedirect('/list-group', '/groups');
Route::permanentRedirect('/list-group/export', '/groups/export');
Route::permanentRedirect('/group/{id}/edit', '/groups/{id}/edit')->whereNumber('id');
Route::permanentRedirect('/ledger', '/ledgers/create');
Route::permanentRedirect('/list-ledger', '/ledgers');
Route::permanentRedirect('/ledger/{id}/edit', '/ledgers/{id}/edit')->whereNumber('id');

//unit
// units.index, units.create, units.store, units.edit, units.update, units.destroy
Route::resource('units', UnitController::class)->except('show')->parameters(['units' => 'id']);

//godown
// godowns.index, godowns.create, godowns.store, godowns.edit, godowns.update, godowns.destroy
Route::resource('godowns', GodownController::class)->except('show')->parameters(['godowns' => 'id']);

//stock group
// stock-groups.index, stock-groups.create, stock-groups.store, stock-groups.edit, stock-groups.update, stock-groups.destroy
Route::resource('stock-groups', StockGroupController::class)->except('show')->parameters(['stock-groups' => 'id']);

//stock category
// stock-categories.index, stock-categories.create, stock-categories.store, stock-categories.edit, ...
Route::resource('stock-categories', StockCategoryController::class)->except('show')->parameters(['stock-categories' => 'id']);

//item
// items.index, items.create, items.store, items.edit, items.update, items.destroy
Route::resource('items', ItemController::class)->except('show')->parameters(['items' => 'id']);

// Copy Allocation Details (old link, no longer in the sidebar) = copy an item: Add Item, starting in its "Copy details from" dropdown
Route::redirect('/copy-allocation-details', '/items/create#copy_from')->name('copy-allocation-details');

//vouchers
// The same set of routes for each type in VoucherController::TYPES (account vouchers:
// /journal + /journal-register, /sales + /sales-register, /purchase, /receipt, /payment, /contra)
// and StockVoucherController::TYPES (inventory vouchers: /material-inward + /material-inward-register).
// defaults('type', ...) tells the controller which voucher type the page is for.
foreach ([VoucherController::class, StockVoucherController::class] as $controller) {
    foreach (array_keys($controller::TYPES) as $type) {
        Route::controller($controller)->group(function () use ($type) {
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
}

// Incoming Vouchers: sales other companies on this software made to this company. OK saves one as a voucher here
// (Purchase, Journal or Payment). The URLs keep their first name, incoming-purchases.
Route::get('/incoming-purchases', [IncomingPurchaseController::class, 'index'])->name('incoming-purchases');
Route::get('/incoming-purchases/{id}', [IncomingPurchaseController::class, 'show'])->whereNumber('id')->name('incoming-purchases.show');
Route::get('/incoming-purchases/{id}/document/{documentId}', [IncomingPurchaseController::class, 'document'])->whereNumber(['id', 'documentId'])->name('incoming-purchases.document');
Route::post('/incoming-purchases/{id}/accept', [IncomingPurchaseController::class, 'accept'])->whereNumber('id')->name('incoming-purchases.accept');
Route::post('/incoming-purchases/{id}/reject', [IncomingPurchaseController::class, 'reject'])->whereNumber('id')->name('incoming-purchases.reject');

// A voucher's document proof (kept on the private disk, so it is opened through the controller)
Route::get('/voucher-document/{id}', [VoucherController::class, 'document'])->whereNumber('id')->name('voucher-document');

// District / City dropdowns of the ledger address fields (filled after picking a State / District)
Route::get('/districts/{stateId}', [LocationController::class, 'districts'])->whereNumber('stateId')->name('districts');
Route::get('/cities/{districtId}', [LocationController::class, 'cities'])->whereNumber('districtId')->name('cities');


// sidebar & header pages (placeholder until each page is built)
foreach ([
    // masters
    'alter', 'shipping-address', 'billing-address',
    'users', 'department', 'position',
    // vouchers
    'credit-note-register', 'cheque-register',
    'material-outward', 'godown-transfer', 'physical-stock',
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

