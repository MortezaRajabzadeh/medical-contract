<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    /**
     * میدل‌ور بررسی دسترسی ادمین
     * فقط کاربران با نوع کاربری admin می‌توانند به پنل ادمین دسترسی داشته باشند
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // بررسی کاربر احراز هویت شده
        if (!Auth::check()) {
            Log::warning('تلاش برای دسترسی به پنل ادمین بدون احراز هویت', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            return redirect()->route('medical-center.login')->with('error', 'لطفا ابتدا وارد سیستم شوید');
        }

        // بررسی نوع کاربری admin
        if (Auth::user()->user_type !== 'admin') {
            Log::warning('تلاش برای دسترسی به پنل ادمین با دسترسی غیرمجاز', [
                'user_id' => Auth::id(),
                'user_type' => Auth::user()->user_type,
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            return redirect()->route('medical-center.dashboard')->with('error', 'شما دسترسی لازم برای ورود به این بخش را ندارید');
        }

        return $next($request);
    }
}
