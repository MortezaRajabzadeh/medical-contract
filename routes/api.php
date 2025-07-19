<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| اینجا می‌توانید مسیرهای API برنامه خود را ثبت کنید. این مسیرها توسط
| RouteServiceProvider بارگذاری می‌شوند و همگی به گروه میانی "api" اختصاص داده می‌شوند.
| حالا وقت آن است که خلاقیت به خرج دهید و مسیرهای خود را ایجاد کنید!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
