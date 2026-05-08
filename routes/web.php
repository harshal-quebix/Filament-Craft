<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\ThemeController;

Route::get('/css/theme.css', [ThemeController::class, 'css'])->name('theme.css');

Route::middleware([\App\Http\Middleware\Setting::class])->group(function () {
    // Landing Page
    Route::get('/', [LandingPageController::class, 'index'])->name('landing');
    Route::get('/about', [LandingPageController::class, 'about'])->name('about');
    Route::get('/contact', [LandingPageController::class, 'contact'])->name('contact');
    Route::post('/contact/save', [LandingPageController::class, 'submitContact'])->name('contact.submit');
    Route::get('/privacy-policy', [LandingPageController::class, 'privacy'])->name('privacy');
    Route::get('/terms-conditions', [LandingPageController::class, 'terms'])->name('terms');
    Route::get('/crud-builder-guide', [LandingPageController::class, 'guide'])->name('guide');

    // Dynamic Content Pages
    Route::get('/page/{slug}', [LandingPageController::class, 'dynamicPage'])->name('page.show');

    // Authentication Routes
    Route::get('/2fa/verify', [AuthController::class, 'show2FAVerify'])->name('2fa.verify');
    Route::post('/2fa/verify', [AuthController::class, 'verify2FA']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('filament.admin.auth.login');
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/password/reset', [AuthController::class, 'showPasswordReset'])->name('password.request');
    Route::post('/password/email', [AuthController::class, 'sendPasswordResetLink'])->name('password.email');
    Route::get('/password/reset/{token}', [AuthController::class, 'showPasswordResetForm'])->name('password.reset');
    Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.update');

    Route::post('/logout', [AuthController::class, 'logout'])->name('filament.admin.auth.logout');

    // Email Verification Routes
    Route::get('/email/verify', [AuthController::class, 'showEmailVerification'])->middleware('auth')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware(['auth', 'signed'])->name('verification.verify');
    Route::get('/email/verification-notification', [AuthController::class, 'sendEmailVerification'])->middleware(['auth', 'throttle:6,1'])->name('verification.send');
});


