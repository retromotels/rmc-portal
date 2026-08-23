<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DetailsController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ChatWidgetController;
use App\Http\Controllers\PublicWidgetController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\Admin\JobAdminController;
use App\Http\Controllers\Jobs\PublicJobController;
use App\Http\Controllers\Jobs\SeekerAuthController;
use App\Http\Controllers\Jobs\ApplicationController;
use App\Models\User;
use App\Http\Controllers\RegistrationController;
use App\Http\Middleware\LogActivity;
use App\Http\Middleware\ResolveProperty;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\ListingController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OnboardController;
use App\Http\Controllers\Admin\OutboxController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Retro Motel Collective — web routes
|--------------------------------------------------------------------------
*/

/*
| Public job board on jobs.retromotels.com (domain-routed, registered first so
| the jobs host resolves here rather than to the portal routes below).
*/
Route::domain(config('rmc.jobs_host'))->group(function () {
    Route::get('/', [PublicJobController::class, 'index'])->name('jobs.board');
    Route::get('/jobs/{slug}', [PublicJobController::class, 'show'])->name('jobs.public.show');

    Route::get('/register', [SeekerAuthController::class, 'showRegister'])->name('seeker.register');
    Route::post('/register', [SeekerAuthController::class, 'register']);
    Route::get('/login', [SeekerAuthController::class, 'showLogin'])->name('seeker.login');
    Route::post('/login', [SeekerAuthController::class, 'login']);
    Route::post('/logout', [SeekerAuthController::class, 'logout'])->name('seeker.logout');

    Route::get('/jobs/{slug}/apply', [ApplicationController::class, 'create'])->name('jobs.apply');
    Route::post('/jobs/{slug}/apply', [ApplicationController::class, 'store']);
    Route::get('/account', [ApplicationController::class, 'dashboard'])->name('seeker.dashboard');
});

Route::get('/', fn () => redirect()->route('register'));

// TEMP: job board preview for staging visual check (remove after verify)
Route::get('/__jobs-preview', [PublicJobController::class, 'index']);
Route::get('/__jobs-preview/{slug}', [PublicJobController::class, 'show']);

// Cheshire Cat microsite (public)
Route::get('/cheshirecat', fn () => view('cheshirecat'))->name('cheshirecat');

// Public embeddable guest chat widget loader (served to properties' own websites)
Route::get('/widget/{token}.js', [PublicWidgetController::class, 'js'])
    ->where('token', '[A-Za-z0-9]+')->name('widget.js');

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

    // Admin-invited property activation
    Route::get('/claim/{token}', [ClaimController::class, 'show'])->name('claim.show');
    Route::post('/claim/{token}', [ClaimController::class, 'store'])->name('claim.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Authenticated owner portal
Route::middleware(['auth', ResolveProperty::class, LogActivity::class])->group(function () {

    // Exit the admin "view as" preview
    Route::post('/preview/exit', function () {
        session()->forget('admin_preview_user_id');
        session()->forget('current_property_id');
        return redirect()->route('admin.overview');
    })->name('preview.exit');

    Route::get('/details', [DetailsController::class, 'show'])->name('details.show');
    Route::post('/details', [DetailsController::class, 'save'])->name('details.save');

    // Multiple properties per account
    Route::post('/properties/switch', [PropertyController::class, 'switch'])->name('properties.switch');
    Route::get('/properties/add', [PropertyController::class, 'add'])->name('properties.add');
    Route::post('/properties', [PropertyController::class, 'store'])->name('properties.store');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/property-setup', [RegistrationController::class, 'index'])->name('registration.index');
    Route::post('/property-setup/{section}', [RegistrationController::class, 'save'])->name('registration.save');
    Route::post('/property-setup/{section}/{field}/upload', [RegistrationController::class, 'upload'])->name('registration.upload');
    Route::get('/uploads/{upload}/download', [RegistrationController::class, 'download'])->name('upload.download');
    Route::delete('/uploads/{upload}', [RegistrationController::class, 'deleteFile'])->name('upload.delete');

    Route::get('/health-check', [HealthController::class, 'index'])->name('health');
    Route::post('/health-check/request/{type}', [HealthController::class, 'request'])->name('health.request');

    Route::get('/about', [PageController::class, 'about'])->name('about');
    Route::get('/faq', [PageController::class, 'faq'])->name('faq');

    // Tools → Chat Widget
    Route::get('/tools/chat-widget', [ChatWidgetController::class, 'edit'])->name('tools.chat-widget');
    Route::put('/tools/chat-widget', [ChatWidgetController::class, 'update']);

    // Jobs (property listings for the public board)
    Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/new', [JobController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/{job}/edit', [JobController::class, 'edit'])->name('jobs.edit');
    Route::put('/jobs/{job}', [JobController::class, 'update'])->name('jobs.update');
    Route::get('/jobs/{job}/applicants', [JobController::class, 'applicants'])->name('jobs.applicants');
    Route::post('/jobs/{job}/close', [JobController::class, 'close'])->name('jobs.close');
    Route::delete('/jobs/{job}', [JobController::class, 'destroy'])->name('jobs.destroy');
    Route::get('/applications/{application}/cv', [JobController::class, 'cvDownload'])->name('jobs.cv');

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
    Route::delete('/motels/{user}', [AdminController::class, 'destroy'])->name('motel.delete');

    // Jobs approval queue
    Route::get('/jobs', [JobAdminController::class, 'index'])->name('jobs');
    Route::post('/jobs/{job}/approve', [JobAdminController::class, 'approve'])->name('jobs.approve');
    Route::post('/jobs/{job}/reject', [JobAdminController::class, 'reject'])->name('jobs.reject');
    Route::get('/jobs/{job}/applicants', [JobAdminController::class, 'applicants'])->name('jobs.applicants');
    Route::get('/policies', [AdminController::class, 'policies'])->name('policies');
    Route::get('/policy/{document}/download', [AdminController::class, 'policyDownload'])->name('policy.download');
    Route::get('/upload/{upload}/download', [AdminController::class, 'uploadDownload'])->name('upload.download');

    // Admin-controlled property content
    Route::get('/content', [ContentController::class, 'edit'])->name('content.edit');
    Route::put('/content', [ContentController::class, 'update'])->name('content.update');

    // Email outbox (preview of queued mail)
    Route::get('/outbox', [OutboxController::class, 'index'])->name('outbox.index');
    Route::post('/outbox/flush', [OutboxController::class, 'flush'])->name('outbox.flush');
    Route::get('/outbox/{outbox}', [OutboxController::class, 'show'])->name('outbox.show');

    // Booking listing analyzer
    Route::get('/listings', [ListingController::class, 'index'])->name('listings.index');
    Route::get('/listings/create', [ListingController::class, 'create'])->name('listings.create');
    Route::post('/listings', [ListingController::class, 'store'])->name('listings.store');
    Route::get('/listings/{listing}', [ListingController::class, 'show'])->name('listings.show');
    Route::put('/listings/{listing}', [ListingController::class, 'update'])->name('listings.update');
    Route::post('/listings/{listing}/reanalyze', [ListingController::class, 'reanalyze'])->name('listings.reanalyze');
    Route::delete('/listings/{listing}', [ListingController::class, 'destroy'])->name('listings.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    // Activity log
    Route::get('/activity', [ActivityController::class, 'index'])->name('activity');

    // Create property + invite link
    Route::get('/onboard', [OnboardController::class, 'create'])->name('onboard.create');
    Route::post('/onboard', [OnboardController::class, 'store'])->name('onboard.store');

    // Per-property image harvester
    Route::get('/motels/{user}/images', [ImageController::class, 'index'])->name('images.index');
    Route::post('/motels/{user}/images/pull', [ImageController::class, 'pull'])->name('images.pull');
    Route::get('/motels/{user}/images/{file}/raw', [ImageController::class, 'raw'])->name('images.raw');
    Route::get('/motels/{user}/images/{file}/download', [ImageController::class, 'download'])->name('images.download');
    Route::get('/motels/{user}/images-zip', [ImageController::class, 'zip'])->name('images.zip');

    // Admin "view as" a specific property — see that property's real portal
    Route::get('/preview/{user}', function (User $user) {
        if ($user->role === 'owner') {
            session(['admin_preview_user_id' => $user->id]);
            session()->forget('current_property_id');
        }
        return redirect()->route('dashboard');
    })->name('preview');
});
