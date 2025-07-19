<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MedicalCenterController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| مسیرهای پنل مدیریت
|--------------------------------------------------------------------------
|
| تمامی مسیرهای مربوط به پنل مدیریت سفارشی در این فایل قرار می‌گیرد.
| تمامی مسیرها با میان‌افزار 'web' و 'auth' محافظت می‌شوند.
|
*/

// اعمال میان‌افزار و پیشوند نام به همه مسیرهای ادمین
// استفاده از میدل‌ور admin برای محدودکردن دسترسی فقط به کاربران با نوع کاربری admin
Route::middleware(['web', 'auth', 'admin'])->name('admin.')->group(function () {
    // مسیر داشبورد اصلی
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // مسیرهای مدیریت مراکز درمانی
    Route::resource('medical-centers', MedicalCenterController::class);
    
    // مسیرهای مدیریت قراردادها
    Route::resource('contracts', ContractController::class);
    
    // مسیرهای اضافی قراردادها
    Route::get('contracts/{contract}/download', [ContractController::class, 'download'])->name('contracts.download');
    Route::get('contracts/{contract}/download-signed', [ContractController::class, 'downloadSigned'])->name('contracts.download-signed');
    Route::post('contracts/{contract}/change-status', [ContractController::class, 'changeStatus'])->name('contracts.change-status');
    
    // مسیرهای مدیریت کاربران
    Route::resource('users', UserController::class);
});
