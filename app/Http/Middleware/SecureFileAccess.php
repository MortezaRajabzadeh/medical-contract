<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Contract;
use Illuminate\Support\Facades\Auth;

class SecureFileAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the contract ID from the route - می‌تواند id یا contract باشد
        $contractId = $request->route('id') ?? $request->route('contract');

        if (!$contractId) {
            // اگر پارامتر مسیر پیدا نشود، ممکن است این مسیر نیازی به این middleware نداشته باشد
            return $next($request);
        }

        // Find the contract or fail with 404
        $contract = Contract::findOrFail($contractId);

        // Check if user has access to this contract
        if (!$this->hasAccess($contract)) {
            abort(403, 'شما اجازه دسترسی به این فایل را ندارید.');
        }

        // Log the file access
        if (Auth::check()) {
            try {
                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($contract)
                    ->withProperties([
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'file' => $contract->file_path,
                    ])
                    ->log('accessed_contract_file');
            } catch (\Exception $e) {
                // اگر ثبت فعالیت با خطا مواجه شود، فقط لاگ می‌کنیم و فرایند را ادامه می‌دهیم
                \Illuminate\Support\Facades\Log::warning('خطا در ثبت فعالیت دسترسی به فایل: ' . $e->getMessage());
            }
        }

        return $next($request);
    }

    /**
     * Check if the authenticated user has access to the contract.
     *
     * @param  \App\Models\Contract  $contract
     * @return bool
     */
    private function hasAccess(Contract $contract): bool
    {
        $user = Auth::user();

        // اگر کاربری لاگین نشده باشد، دسترسی رد می‌شود
        if (!$user) {
            return false;
        }

        // مدیر سیستم به همه فایل‌ها دسترسی دارد
        if ($user->hasRole('system_admin')) {
            return true;
        }

        // سازنده قرارداد به آن دسترسی دارد
        if ($user->id === $contract->created_by) {
            return true;
        }

        // بررسی تعلق کاربر به مرکز درمانی مرتبط با قرارداد
        if ($user->medical_center_id === $contract->medical_center_id) {
            // کاربران مرکز درمانی با نقش‌های مختلف دسترسی دارند
            if ($user->hasRole('medical_admin') || $user->hasRole('medical_staff') || $user->hasRole('viewer')) {
                return true;
            }
            
            // بررسی مجوزهای کاربر (مستقیم یا از طریق نقش)
            if ($user->hasPermissionTo('view_contracts') || $user->hasPermissionTo('download_files')) {
                return true;
            }
        }

        return false;
    }
}
