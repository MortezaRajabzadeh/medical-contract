<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * تعیین مسیر ریدایرکت کاربر در صورت عدم احراز هویت
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo(Request $request): ?string
    {
        // اگر درخواست API است، نیازی به ریدایرکت نیست
        if ($request->expectsJson()) {
            return null;
        }

        // اگر کاربر احراز هویت نشده است، به مسیر لاگین مرکز درمانی ریدایرکت شود
        return route('medical-center.login');
    }
}
