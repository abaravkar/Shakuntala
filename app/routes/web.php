<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SubmissionController;
use App\Http\Middleware\RequireAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'page'])->name('home');
Route::get('/franchise/application', fn (Request $request) => app(SiteController::class)->page($request, 'application'));
Route::get('/franchise/agreement', fn (Request $request) => app(SiteController::class)->page($request, 'agreement'));
Route::get('/distribution/application', fn (Request $request) => app(SiteController::class)->page($request, 'distribution-application'));
Route::get('/partners/onboarding', fn (Request $request) => app(SiteController::class)->page($request, 'onboarding'));
Route::post('/inquiries', [SubmissionController::class, 'inquiry'])->middleware('throttle:5,1');
Route::post('/franchise/applications', [SubmissionController::class, 'application'])->middleware('throttle:3,1');
Route::get('/applications/{application:reference}/status', [PaymentController::class, 'status'])->middleware('signed')->name('application.status');
Route::post('/applications/{application:reference}/payment', [PaymentController::class, 'submit'])->middleware(['signed', 'throttle:5,1'])->name('application.payment');
Route::post('/applications/{application:reference}/online', [PaymentController::class, 'online'])->middleware(['signed', 'throttle:3,1'])->name('application.online');
Route::post('/payments/razorpay/webhook', [PaymentController::class, 'webhook'])->middleware('throttle:120,1');
Route::get('/payments/qr', [PaymentController::class, 'qr']);
Route::get('/admin/login', fn () => view('admin.login'))->name('login');
Route::post('/admin/login', [AdminController::class, 'login'])->middleware('throttle:5,1');
Route::middleware(RequireAdmin::class)->prefix('admin')->group(function () {
    Route::get('/instagram', [\App\Http\Controllers\InstagramController::class, 'settings']);
    Route::post('/instagram/sync', [\App\Http\Controllers\InstagramController::class, 'sync'])->middleware('throttle:3,1');
    Route::get('/franchise-models', [\App\Http\Controllers\FranchiseModelController::class, 'index']);
    Route::get('/franchise-models/new', [\App\Http\Controllers\FranchiseModelController::class, 'edit']);
    Route::post('/franchise-models', [\App\Http\Controllers\FranchiseModelController::class, 'save']);
    Route::get('/franchise-models/{model}/edit', [\App\Http\Controllers\FranchiseModelController::class, 'edit']);
    Route::put('/franchise-models/{model}', [\App\Http\Controllers\FranchiseModelController::class, 'save']);
    Route::get('/', [AdminController::class, 'dashboard']);
    Route::post('/logout', [AdminController::class, 'logout']);
    Route::get('/content/new', [AdminController::class, 'edit']);
    Route::post('/content', [AdminController::class, 'save']);
    Route::get('/content/{item}/edit', [AdminController::class, 'edit']);
    Route::put('/content/{item}', [AdminController::class, 'save']);
    Route::patch('/inquiries/{inquiry}', [AdminController::class, 'status']);
    Route::get('/applications/{application}', [AdminController::class, 'application']);
    Route::post('/payments/{payment}/review', [PaymentController::class, 'review']);
    Route::get('/payments/{payment}/proof', [PaymentController::class, 'proof']);
    Route::post('/applications/{application}/review', [PaymentController::class, 'advance']);
    Route::get('/payment-settings', [PaymentController::class, 'settings']);
    Route::post('/payment-settings', [PaymentController::class, 'saveSettings']);
});
Route::get('/{page}', [SiteController::class, 'page'])->where('page', 'about|menu|locations|franchise|distribution|moments|blog|contact|instagram|social|terms|privacy|refund');
