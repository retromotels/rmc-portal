<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DetailsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\ListingController;
use App\Http\Controllers\Admin\OutboxController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Retro Motel Collective — web routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('register'));

// Guest auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [PasswordController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'sendReset'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Authenticated owner portal
Route::middleware('auth')->group(function () {

    Route::get('/details', [DetailsController::class, 'show'])->name('details.show');
    Route::post('/details', [DetailsController::class, 'save'])->name('details.save');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/property-setup', [RegistrationController::class, 'index'])->name('registration.index');
    Route::post('/property-setup/{section}', [RegistrationController::class, 'save'])->name('registration.save');
    Route::post('/property-setup/{section}/{field}/upload', [RegistrationController::class, 'upload'])->name('registration.upload');
    Route::get('/uploads/{upload}/download', [RegistrationController::class, 'download'])->name('upload.download');
    Route::delete('/uploads/{upload}', [RegistrationController::class, 'deleteFile'])->name('upload.delete');

    Route::get('/website-checker', [CheckerController::class, 'index'])->name('checker');

    Route::get('/about', [PageController::class, 'about'])->name('about');
    Route::get('/faq', [PageController::class, 'faq'])->name('faq');

    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::post('/account', [AccountController::class, 'update'])->name('account.update');
    Route::get('/account/policy/{document}', [AccountController::class, 'policyDownload'])->name('account.policy');
    Route::post('/account/cancel', [AccountController::class, 'requestCancellation'])->name('account.cancel');
});

// Admin (head office)
Route::middleware(['auth', EnsureAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'overview'])->name('overview');
    Route::get('/motels', [AdminController::class, 'motels'])->name('motels');
    Route::get('/motels/{user}', [AdminController::class, 'motel'])->name('motel');
    Route::get('/policies', [AdminController::class, 'policies'])->name('policies');
    Route::get('/policy/{document}/download', [AdminController::class, 'policyDownload'])->name('policy.download');
    Route::get('/upload/{upload}/download', [AdminController::class, 'uploadDownload'])->name('upload.download');

    // Admin-controlled property content
    Route::get('/content', [ContentController::class, 'edit'])->name('content.edit');
    Route::put('/content', [ContentController::class, 'update'])->name('content.update');

    // Email outbox (preview of queued mail)
    Route::get('/outbox', [OutboxController::class, 'index'])->name('outbox.index');
    Route::get('/outbox/{outbox}', [OutboxController::class, 'show'])->name('outbox.show');

    // Booking listing analyzer
    Route::get('/listings', [ListingController::class, 'index'])->name('listings.index');
    Route::get('/listings/create', [ListingController::class, 'create'])->name('listings.create');
    Route::post('/listings', [ListingController::class, 'store'])->name('listings.store');
    Route::get('/listings/{listing}', [ListingController::class, 'show'])->name('listings.show');
    Route::put('/listings/{listing}', [ListingController::class, 'update'])->name('listings.update');
    Route::post('/listings/{listing}/reanalyze', [ListingController::class, 'reanalyze'])->name('listings.reanalyze');
    Route::delete('/listings/{listing}', [ListingController::class, 'destroy'])->name('listings.destroy');
});
