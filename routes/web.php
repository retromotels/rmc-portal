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
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\VettingController;
use App\Http\Controllers\Admin\DocumentAdminController;
use App\Http\Controllers\Admin\SupplierAdminController;
use App\Http\Controllers\PublicWidgetController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\Admin\JobAdminController;
use App\Http\Controllers\Admin\SeekerAdminController;
use App\Http\Controllers\Jobs\PublicJobController;
use App\Http\Controllers\Jobs\SeekerAuthController;
use App\Http\Controllers\Jobs\SeekerProfileController;
use App\Http\Controllers\Jobs\ApplicationController;
use App\Http\Controllers\Jobs\EmployerAuthController;
use App\Http\Controllers\Jobs\EmployerController;
use App\Http\Controllers\Jobs\EmployerBillingController;
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

    // Seeker profile: details, photo, resume library
    Route::get('/account/profile', [SeekerProfileController::class, 'show'])->name('seeker.profile');
    Route::post('/account/profile', [SeekerProfileController::class, 'update'])->name('seeker.profile.update');
    Route::post('/account/avatar', [SeekerProfileController::class, 'uploadAvatar'])->name('seeker.avatar.upload');
    Route::get('/account/avatar/{seeker}', [SeekerProfileController::class, 'avatar'])->name('seeker.avatar');
    Route::post('/account/resumes', [SeekerProfileController::class, 'addResume'])->name('seeker.resume.add');
    Route::post('/account/resumes/{resume}/default', [SeekerProfileController::class, 'setDefaultResume'])->name('seeker.resume.default');
    Route::delete('/account/resumes/{resume}', [SeekerProfileController::class, 'deleteResume'])->name('seeker.resume.delete');
});

/*
| External (non-member) employers — host-agnostic so they work on the jobs host
| in production and on staging for review. Controllers are feature-flagged.
*/
Route::group([], function () {
    Route::get('/post-a-job', [EmployerController::class, 'pricing'])->name('employers.pricing');
    Route::get('/employers/register', [EmployerAuthController::class, 'showRegister'])->name('employer.register');
    Route::post('/employers/register', [EmployerAuthController::class, 'register']);
    Route::get('/employers/login', [EmployerAuthController::class, 'showLogin'])->name('employer.login');
    Route::post('/employers/login', [EmployerAuthController::class, 'login']);
    Route::post('/employers/logout', [EmployerAuthController::class, 'logout'])->name('employer.logout');
    Route::get('/employers/dashboard', [EmployerController::class, 'dashboard'])->name('employer.dashboard');
    Route::get('/employers/post', [EmployerController::class, 'createJob'])->name('employer.job.create');
    Route::post('/employers/post', [EmployerController::class, 'storeJob'])->name('employer.job.store');
    Route::post('/employers/buy/{tier}', [EmployerBillingController::class, 'checkout'])->name('employer.buy');
    Route::get('/employers/buy/success', [EmployerBillingController::class, 'success'])->name('employer.buy.success');
    Route::get('/employers/buy/cancel', [EmployerBillingController::class, 'cancel'])->name('employer.buy.cancel');
    Route::post('/employers/enquire', [EmployerBillingController::class, 'enquire'])->name('employer.enquire');
});

Route::get('/', fn () => redirect()->route('register'));

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

    // Tools → SOP Documents (feature-flagged)
    Route::get('/tools/documents', [DocumentController::class, 'index'])->name('tools.documents');
    Route::get('/tools/documents/{document}', [DocumentController::class, 'show'])->name('tools.documents.show');
    Route::post('/tools/documents/{document}/download', [DocumentController::class, 'download'])->name('tools.documents.download');

    // Tools → The Vetting Desk (feature-flagged)
    Route::get('/tools/vetting', [VettingController::class, 'index'])->name('tools.vetting');
    Route::post('/tools/vetting/lookup', [VettingController::class, 'lookup'])->name('tools.vetting.lookup');
    Route::post('/tools/vetting', [VettingController::class, 'run'])->name('tools.vetting.run');
    Route::get('/tools/vetting/{vetCheck}', [VettingController::class, 'result'])->name('tools.vetting.result');

    // Tools → Suppliers directory (feature-flagged)
    Route::get('/tools/suppliers', [SupplierController::class, 'index'])->name('tools.suppliers');
    Route::get('/tools/suppliers/{supplier}', [SupplierController::class, 'show'])->name('tools.suppliers.show');
    Route::post('/tools/suppliers/{supplier}/save', [SupplierController::class, 'toggleSave'])->name('tools.suppliers.save');
    Route::post('/tools/suppliers/{supplier}/request', [SupplierController::class, 'sendRequest'])->name('tools.suppliers.request');

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
    Route::get('/jobs/create', [JobAdminController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [JobAdminController::class, 'store'])->name('jobs.store');
    Route::get('/applications/{application}/cv', [JobAdminController::class, 'cvDownload'])->name('jobs.appcv');
    Route::post('/jobs/{job}/approve', [JobAdminController::class, 'approve'])->name('jobs.approve');
    Route::post('/jobs/{job}/reject', [JobAdminController::class, 'reject'])->name('jobs.reject');
    Route::get('/jobs/{job}/applicants', [JobAdminController::class, 'applicants'])->name('jobs.applicants');

    // Job-seeker CRM (registered applicants)
    Route::get('/seekers', [SeekerAdminController::class, 'index'])->name('seekers');
    Route::get('/seekers/{seeker}/cv', [SeekerAdminController::class, 'cvDownload'])->name('seeker.cv');

    Route::get('/policies', [AdminController::class, 'policies'])->name('policies');
    Route::get('/policy/{document}/download', [AdminController::class, 'policyDownload'])->name('policy.download');
    Route::get('/upload/{upload}/download', [AdminController::class, 'uploadDownload'])->name('upload.download');

    // Admin-controlled property content
    Route::get('/content', [ContentController::class, 'edit'])->name('content.edit');
    Route::put('/content', [ContentController::class, 'update'])->name('content.update');

    // SOP Documents (feature-flagged)
    Route::get('/documents', [DocumentAdminController::class, 'index'])->name('documents');
    Route::get('/documents/create', [DocumentAdminController::class, 'create'])->name('documents.create');
    Route::post('/documents', [DocumentAdminController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/edit', [DocumentAdminController::class, 'edit'])->name('documents.edit');
    Route::put('/documents/{document}', [DocumentAdminController::class, 'update'])->name('documents.update');
    Route::delete('/documents/{document}', [DocumentAdminController::class, 'destroy'])->name('documents.destroy');
    Route::get('/documents/{document}/stats', [DocumentAdminController::class, 'stats'])->name('documents.stats');

    // Suppliers directory (feature-flagged)
    Route::get('/suppliers', [SupplierAdminController::class, 'index'])->name('suppliers');
    Route::get('/suppliers/create', [SupplierAdminController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [SupplierAdminController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}/edit', [SupplierAdminController::class, 'edit'])->name('suppliers.edit');
    Route::put('/suppliers/{supplier}', [SupplierAdminController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [SupplierAdminController::class, 'destroy'])->name('suppliers.destroy');
    Route::get('/suppliers/requests', [SupplierAdminController::class, 'requests'])->name('suppliers.requests');

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
