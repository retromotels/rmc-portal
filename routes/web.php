<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DetailsController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Retro Motel Collective — web routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('login'));

// Public microsites (published pages are indexable; preview tokens are noindex + password-gated)
Route::get('/motel/{key}', [PublicSiteController::class, 'show'])->name('site.show');
Route::post('/motel/{key}/unlock', [PublicSiteController::class, 'unlock'])->name('site.unlock');
Route::get('/motel/{key}/{page}', [PublicSiteController::class, 'page'])->name('site.page');

// Guest auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
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

    // Site Builder
    Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
    Route::get('/sites/create', [SiteController::class, 'create'])->name('sites.create');
    Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
    Route::get('/sites/{site}/edit', [SiteController::class, 'edit'])->name('sites.edit');
    Route::put('/sites/{site}', [SiteController::class, 'update'])->name('sites.update');
    Route::post('/sites/{site}/rescrape', [SiteController::class, 'rescrape'])->name('sites.rescrape');
    Route::post('/sites/{site}/publish', [SiteController::class, 'togglePublish'])->name('sites.publish');
    Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->name('sites.destroy');
    // Internal pages
    Route::post('/sites/{site}/crawl', [SiteController::class, 'recrawlPages'])->name('sites.recrawl');
    Route::get('/sites/{site}/pages/{page}/edit', [SiteController::class, 'editPage'])->name('sites.page.edit');
    Route::put('/sites/{site}/pages/{page}', [SiteController::class, 'updatePage'])->name('sites.page.update');
    Route::delete('/sites/{site}/pages/{page}', [SiteController::class, 'destroyPage'])->name('sites.page.destroy');
});
