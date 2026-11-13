<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LifetimeRequestController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('pages.home'))->name('home');
Route::get('/compare', [CompareController::class, 'index'])->name('compare');
Route::post('/compare', [CompareController::class, 'run'])->middleware('throttle:'.config('codediff.guest.requests_per_minute').',1')->name('compare.run');
Route::get('/pricing', fn () => view('pages.pricing'))->name('pricing');
Route::get('/features', fn () => view('pages.features'))->name('features');
Route::get('/privacy', fn () => view('pages.privacy'))->name('privacy');
Route::get('/terms', fn () => view('pages.terms'))->name('terms');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/forgot-password', [AuthController::class, 'forgotForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', fn () => view('auth.verify-email'))->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('dashboard')->with('status', 'Your email is verified.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'A fresh verification link has been sent.');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('verified')->name('dashboard');
    Route::get('/my-comparisons', [DashboardController::class, 'comparisons'])->middleware('verified')->name('comparisons.index');
    Route::delete('/my-comparisons/{comparison}', [DashboardController::class, 'deleteComparison'])->middleware('verified')->name('comparisons.delete');
    Route::get('/lifetime-access', [LifetimeRequestController::class, 'index'])->middleware('verified')->name('lifetime.index');
    Route::post('/lifetime-access', [LifetimeRequestController::class, 'store'])->middleware('verified')->name('lifetime.store');
    Route::post('/notifications/read', [DashboardController::class, 'readNotifications'])->name('notifications.read');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/access-grants', [AdminController::class, 'grants'])->name('grants');
    Route::get('/lifetime-requests', [AdminController::class, 'requests'])->name('requests');
    Route::post('/lifetime-requests/{lifetimeRequest}/{decision}', [AdminController::class, 'review'])
        ->whereIn('decision', ['approve', 'reject'])->name('requests.review');
    Route::post('/users/{user}/lifetime-grant', [AdminController::class, 'grant'])->name('users.grant');
    Route::post('/entitlements/{entitlement}/revoke', [AdminController::class, 'revoke'])->name('entitlements.revoke');
    Route::get('/audit-log', [AdminController::class, 'auditLog'])->name('audit');
});
