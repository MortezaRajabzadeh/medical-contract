<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicalCenterAuthController;
use App\Http\Controllers\MedicalCenterDashboardController;
use App\Http\Controllers\MedicalCenterContractController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('medical-center.login');
});

Route::get('/login', function () {
    return redirect()->route('medical-center.login');
});
Route::get('/logout', function () {
    return redirect()->route('medical-center.login');
});

// Medical Center Authentication Routes
Route::prefix('medical-center')->name('medical-center.')->group(function () {
    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [MedicalCenterAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [MedicalCenterAuthController::class, 'login'])->name('login.submit');

        // Password Reset Routes
        Route::get('/forgot-password', [MedicalCenterAuthController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('/forgot-password', [MedicalCenterAuthController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('/reset-password/{token}', [MedicalCenterAuthController::class, 'showResetForm'])->name('password.reset');
        Route::post('/reset-password', [MedicalCenterAuthController::class, 'resetPassword'])->name('password.update');
    });

    // Authenticated routes
    Route::middleware(['auth'])->group(function () { // حذف موقت middleware نقش
        Route::get('/dashboard', [MedicalCenterAuthController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [MedicalCenterAuthController::class, 'logout'])->name('logout');

        // Contract Management Routes - با استفاده از Resource Controller و مدیریت مجوز
        Route::prefix('contracts')->name('contracts.')->group(function () {
            // مسیر لیست قراردادها
            Route::get('/', [MedicalCenterContractController::class, 'index'])->name('index');

            // مسیر نمایش قرارداد با middleware مجوز
            Route::get('/{id}/view', [MedicalCenterContractController::class, 'show'])
                ->name('view');

            // مسیر دانلود قرارداد با middleware مجوز
            Route::get('/{id}/download', [MedicalCenterContractController::class, 'download'])
                ->name('download');
                
            // مسیر نمایش مستقیم فایل PDF برای iframe
            Route::get('/{id}/file', [MedicalCenterContractController::class, 'viewFile'])
                ->name('file');

            // مسیر دانلود قرارداد امضا شده - باید به کنترلر resource منتقل شود
            Route::get('/{id}/download-signed', [MedicalCenterDashboardController::class, 'downloadSignedContract'])
                ->name('download.signed');
        });
    });
});

// بارگذاری مسیرهای پنل ادمین
require __DIR__ . '/admin.php';
