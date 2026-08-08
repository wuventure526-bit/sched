<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\UsageController;
use App\Http\Controllers\UnitAdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\SsoController;
use App\Http\Controllers\RequestDocumentController;
use App\Http\Controllers\RequestApprovalController;

//
// ROOT
//
Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});

//
// AUTH
//
Route::get('/register', [RegisterController::class, 'create'])->name('register.create');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::get('/login', [SessionsController::class, 'create'])->name('login.create');
Route::post('/login', [SessionsController::class, 'store'])->name('login.store');

Route::post('/logout', [SessionsController::class, 'destroy'])->name('logout');


//
// AUTHENTICATED AREA
//
Route::middleware(['auth'])->group(function () {

    Route::get('/sso/login', [SsoController::class, 'loginWithToken'])->name('sso.login');

    Route::get('/plan', [\App\Http\Controllers\PlanSsoController::class, 'redirectToPlan'])->name('plan.launch');

    //
    // DASHBOARD
    //
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard.index');

    //
    // PROFILE
    //
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::post('/profile/update-photo', [ProfileController::class, 'updatePhoto'])->name('profile.updatePhoto');

    Route::get('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.changePassword');
    Route::put('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');


    // =========================
    // REQUEST MODULE (ALL USERS)
    // =========================
    Route::get('/requests', [RequestDocumentController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [RequestDocumentController::class, 'create'])->name('requests.create');
    Route::post('/requests', [RequestDocumentController::class, 'store'])->name('requests.store');

    Route::get('/requests/{requestDocument}', [RequestDocumentController::class, 'show'])->name('requests.show');

    // Edit/Update (controller can enforce owner rules)
    Route::get('/requests/{requestDocument}/edit', [RequestDocumentController::class, 'edit'])->name('requests.edit');
    Route::put('/requests/{requestDocument}', [RequestDocumentController::class, 'update'])->name('requests.update');

    // Workflow actions
    Route::post('/requests/{requestDocument}/submit', [RequestDocumentController::class, 'submit'])->name('requests.submit');

    // NOTE (Unit Admin OR Borrower OR Admin)  ? uses existing middleware
    Route::post('/requests/{requestDocument}/note', [RequestDocumentController::class, 'note'])
        ->middleware(['unitadminorborrower'])
        ->name('requests.note');

    // APPROVE/REJECT (Admin only)
    Route::post('/requests/{requestDocument}/approve', [RequestDocumentController::class, 'approve'])
        ->middleware(['administrator'])
        ->name('requests.approve');

    Route::post('/requests/{requestDocument}/reject', [RequestDocumentController::class, 'reject'])
        ->middleware(['administrator'])
        ->name('requests.reject');

    // Print/PDF (controller restricts to approved)
    Route::get('/requests/{requestDocument}/print', [RequestDocumentController::class, 'print'])->name('requests.print');
    Route::get('/requests/{requestDocument}/pdf', [RequestDocumentController::class, 'pdf'])->name('requests.pdf');


    // ==================================
    // REQUEST APPROVAL INBOX
    // Unit Admin OR Borrower OR Admin ? uses existing middleware
    // ==================================
    Route::prefix('requests/approval')
        ->name('requests.approval.')
        ->middleware(['unitadminorborrower'])
        ->group(function () {
            Route::get('/', [RequestApprovalController::class, 'index'])->name('index');
        });
});


//
// =========================
// ADMINISTRATOR ACCESS
// =========================
Route::middleware(['auth', 'administrator'])->group(function () {

    // Unit Admin management
    Route::get('/unitadmins', [UnitAdminController::class, 'index'])->name('unitadmins.index');
    Route::post('/unitadmins', [UnitAdminController::class, 'store'])->name('unitadmins.store');
    Route::get('/unitadmins/create', [UnitAdminController::class, 'create'])->name('unitadmins.create');
    Route::get('/unitadmins/{unitadmin}/show', [UnitAdminController::class, 'show'])->name('unitadmins.show');
    Route::get('/unitadmins/{unitadmin}/edit', [UnitAdminController::class, 'edit'])->name('unitadmins.edit');
    Route::put('/unitadmins/{unitadmin}/update', [UnitAdminController::class, 'update'])->name('unitadmins.update');
    Route::delete('/unitadmins/{unitadmin}', [UnitAdminController::class, 'destroy'])->name('unitadmins.destroy');

    // Units
    Route::get('/units', [UnitController::class, 'index'])->name('units.index');
    Route::get('/units/{unit}/show', [UnitController::class, 'show'])->name('units.show');
    Route::post('/units', [UnitController::class, 'store'])->name('units.store');
    Route::get('/units/create', [UnitController::class, 'create'])->name('units.create');
    Route::get('/units/{unit}/edit', [UnitController::class, 'edit'])->name('units.edit');
    Route::put('/units/{unit}/update', [UnitController::class, 'update'])->name('units.update');
    Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/{category}/show', [CategoryController::class, 'show'])->name('categories.show');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}/update', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
});


//
// =========================
// UNITADMIN ONLY (keep strict)
// =========================
Route::middleware(['auth', 'unitadmin'])->group(function () {

    // Usages list + edit
    Route::get('/usages', [UsageController::class, 'index'])->name('usages.index');
    Route::get('/usages/{usage}/show', [UsageController::class, 'show'])->name('usages.show');

    Route::get('/usages/{usage}/edit', [UsageController::class, 'edit'])->name('usages.edit');
    Route::put('/usages/{usage}/update', [UsageController::class, 'update'])->name('usages.update');
    Route::put('/item/{usage}/set-used', [UsageController::class, 'setUsed'])->name('usages.set-used');

    // Return items
    Route::get('usages/{usage}/return', [ReturnController::class, 'show'])->name('usages.return.show');
    Route::put('usages/{usage}/return', [ReturnController::class, 'return'])->name('usages.return');
});


//
// =========================
// BORROWER ONLY (keep strict)
// =========================
Route::middleware(['auth', 'borrower'])->group(function () {

    // Booking create. The matching store route lives in the shared
    // unitadminorborrower group below; it previously existed here too under the
    // same name, which left two different URIs claiming "bookings.store" and
    // made route caching (and therefore production) impossible.
    Route::get('/bookings/{item}', [BookingController::class, 'create'])->name('bookings.create');

    // Cancel booking
    Route::get('/bookings/{booking}/cancel', [ApprovalController::class, 'cancel'])->name('bookings.cancel');

    // Print approval letter
    Route::get('/bookings/{booking}/approval', [ApprovalController::class, 'generateApprovalLetter'])->name('bookings.approval');
});


//
// =========================
// UNITADMIN OR BORROWER (Admin allowed via middleware logic)
// =========================
Route::middleware(['auth', 'unitadminorborrower'])->group(function () {

    // Items
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/items/{item}/show', [ItemController::class, 'show'])->name('items.show');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');

    // Bookings list/show/update
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}/show', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');

    // Approval booking
    Route::get('/bookings/{booking}/approve', [ApprovalController::class, 'show'])->name('bookings.approve.show');
    Route::post('/bookings/{booking}', [ApprovalController::class, 'approve'])->name('bookings.approve');
    Route::get('/bookings/{booking}/reject', [ApprovalController::class, 'reject'])->name('bookings.reject');
});