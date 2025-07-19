<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Contract;
use App\Models\MedicalCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Cache\RateLimiter;
use Carbon\Carbon;

class MedicalCenterAuthController extends Controller
{

    /**
     * نمایش فرم ورود برای کاربران مرکز پزشکی
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.medical-center-login');
    }

    /**
     * پردازش درخواست ورود به برنامه
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // محدودکننده ورود به صورت دستی
        $limiter = app(RateLimiter::class);
        $key = $this->throttleKey($request);

        // بررسی تعداد تلاش‌های ناموفق
        if ($limiter->tooManyAttempts($key, 5)) {
            $seconds = $limiter->availableIn($key);

            return redirect()
                ->route('medical-center.login')
                ->with('error', 'تعداد تلاش‌های ناموفق بیش از حد مجاز بوده است. لطفاً پس از ' . $seconds . ' ثانیه مجدداً تلاش کنید.')
                ->withInput($request->except('password'));
        }

        if ($this->attemptLogin($request)) {
            // پاک کردن محدودیت‌های ورود در صورت موفقیت
            $limiter->clear($key);
            return $this->sendLoginResponse($request);
        }

        // افزایش تعداد تلاش‌های ناموفق
        $limiter->hit($key, 60);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * اعتبارسنجی درخواست ورود کاربر
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * تلاش برای ورود کاربر به برنامه
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);
        $remember = $request->filled('remember');
        
        try {
            // First check if the user exists and credentials match
            $authenticated = Auth::attempt($credentials, $remember);
            
            if ($authenticated) {
                // Then check if the user is active and belongs to a medical center
                $user = Auth::user();
                
                if (!$user->is_active || !$user->medical_center_id) {
                    Auth::logout();
                    return false;
                }
                
                // Record the last login time
                $user->last_login_at = Carbon::now();
                $user->save();
                
                // برای اطمینان از عملکرد مرا به خاطر بسپار
                if ($remember) {
                    // اطمینان از ایجاد توکن remember
                    $user->setRememberToken(Str::random(60));
                    $user->save();
                    
                    // ثبت لاگ برای ردیابی
                    Log::info('گزینه مرا به خاطر بسپار فعال شد', ['user_id' => $user->id]);
                }
                
                // Log activity
                Log::info('کاربر مرکز پزشکی وارد سیستم شد', [
                    'user_id' => $user->id,
                    'remember_me' => $remember ? 'فعال' : 'غیرفعال'
                ]);
                
                return true;
            }
        } catch (\Exception $e) {
            Log::error('خطا در فرایند احراز هویت: ' . $e->getMessage(), [
                'email' => $credentials[$this->username()],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return false;
    }

    /**
     * دریافت اعتبارنامه‌های مورد نیاز از درخواست
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    /**
     * ارسال پاسخ پس از احراز هویت کاربر
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        return redirect()->intended(route('medical-center.dashboard'));
    }

    /**
     * کاربر احراز هویت شده است - هدایت به مسیر مناسب بر اساس نوع کاربری
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // لاگ ورود موفق به سیستم
        Log::info('کاربر با موفقیت وارد سیستم شد', [
            'user_id' => $user->id,
            'user_type' => $user->user_type,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);
        
        // ریدایرکت بر اساس نوع کاربری
        if ($user->user_type === 'admin') {
            // کاربر ادمین به پنل ادمین هدایت می‌شود
            return redirect()->route('admin.dashboard');
        } else {
            // سایر کاربران به داشبورد مرکز پزشکی هدایت می‌شوند
            return redirect()->route('medical-center.dashboard');
        }
    }

    /**
     * دریافت نمونه پاسخ ورود ناموفق
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        return redirect()->route('medical-center.login')
            ->withInput($request->only($this->username()))
            ->withErrors([
                $this->username() => 'اطلاعات ورود نادرست است. لطفاً مجدداً تلاش کنید.',
            ]);
    }

    /**
     * خروج کاربر از سیستم
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('medical-center.login');
    }

    /**
     * دریافت نام کاربری مورد استفاده توسط کنترلر
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }

    /**
     * ایجاد کلید برای محدودسازی تلاش‌های ورود
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function throttleKey(Request $request)
    {
        return 'login_throttle|' . $request->ip() . '|' . $request->input($this->username());
    }
    
    /**
     * نمایش فرم درخواست لینک بازنشانی رمز عبور
     *
     * @return \Illuminate\View\View
     */
    public function showForgotPasswordForm()
    {
        return view('auth.medical-center-forgot-password');
    }

    /**
     * ارسال لینک بازنشانی رمز عبور به کاربر
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withInput($request->only('email'))->withErrors(['email' => 'امکان ارسال لینک بازنشانی رمز عبور وجود ندارد. لطفاً با مدیر سیستم تماس بگیرید.']);
    }

    /**
     * Display the password reset view for the given token.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.medical-center-reset-password')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        // در اینجا تلاش می‌کنیم رمز عبور کاربر را بازنشانی کنیم. اگر موفق بود
        // رمز عبور را در مدل کاربر به‌روزرسانی و در پایگاه داده ذخیره می‌کنیم.
        // در غیر این صورت خطا را تجزیه و تحلیل و پاسخ را برمی‌گردانیم.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
                
                // Log activity
                Log::info('Password reset completed', ['user_id' => $user->id]);
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('medical-center.login')->with('status', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
    }
    
    /**
     * Display the medical center dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        try {
            $user = Auth::user();
            $medicalCenter = MedicalCenter::findOrFail($user->medical_center_id);
            
            // Get recent contracts
            $contracts = Contract::where('medical_center_id', $medicalCenter->id)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
            
            // Log activity
            Log::info('Viewed medical center dashboard', ['user_id' => $user->id]);
                
            return view('medical-center.dashboard', [
                'medicalCenter' => $medicalCenter,
                'contracts' => $contracts,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('داشبورد مرکز درمانی با خطا مواجه شد: ' . $e->getMessage());
            return redirect()->route('medical-center.login')
                ->with('error', 'خطایی در بارگزاری داشبورد رخ داد. لطفا دوباره وارد شوید.');
        }
    }
    
    /**
     * List all contracts for the medical center.
     *
     * @return \Illuminate\View\View
     */
    public function listContracts()
    {
        try {
            $user = Auth::user();
            $medicalCenter = MedicalCenter::findOrFail($user->medical_center_id);
            
            $contracts = Contract::where('medical_center_id', $medicalCenter->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            
            // Log activity
            Log::info('Listed all contracts', ['user_id' => $user->id]);
                
            return view('medical-center.contracts.index', [
                'medicalCenter' => $medicalCenter,
                'contracts' => $contracts
            ]);
        } catch (\Exception $e) {
            Log::error('لیست قراردادهای مرکز درمانی با خطا مواجه شد: ' . $e->getMessage());
            return redirect()->route('medical-center.dashboard')
                ->with('error', 'خطایی در بارگزاری لیست قراردادها رخ داد. لطفا مجددا تلاش کنید.');
        }
    }
    
    /**
     * View a specific contract.
     *
     * @param  \App\Models\Contract  $contract
     * @return \Illuminate\View\View
     */
    public function viewContract(Contract $contract)
    {
        try {
            $user = Auth::user();
            
            // Check if this contract belongs to the user's medical center
            if ($contract->medical_center_id != $user->medical_center_id) {
                return redirect()->route('medical-center.contracts.index')
                    ->with('error', 'شما به این قرارداد دسترسی ندارید.');
            }
            
            // Log activity
            Log::info('Viewed contract', ['user_id' => $user->id, 'contract_id' => $contract->id]);
                
            return view('medical-center.contracts.view', [
                'contract' => $contract
            ]);
        } catch (\Exception $e) {
            Log::error('مشاهده قرارداد با خطا مواجه شد: ' . $e->getMessage());
            return redirect()->route('medical-center.contracts.index')
                ->with('error', 'خطایی در بارگزاری قرارداد رخ داد. لطفا مجددا تلاش کنید.');
        }
    }
    
    /**
     * Download a contract file.
     *
     * @param  \App\Models\Contract  $contract
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadContract(Contract $contract)
    {
        try {
            $user = Auth::user();
            
            // Check if this contract belongs to the user's medical center
            if ($contract->medical_center_id != $user->medical_center_id) {
                return redirect()->route('medical-center.contracts.index')
                    ->with('error', 'شما به این قرارداد دسترسی ندارید.');
            }
            
            // Check if file exists
            if (!Storage::disk('private')->exists($contract->file_path)) {
                return redirect()->route('medical-center.contracts.view', $contract)
                    ->with('error', 'فایل قرارداد یافت نشد.');
            }
            
            // Log activity
            Log::info('Downloaded contract file', ['user_id' => $user->id, 'contract_id' => $contract->id]);
                
            return Storage::disk('private')->download(
                $contract->file_path,
                $contract->original_filename ?: 'contract.pdf'
            );
        } catch (\Exception $e) {
            Log::error('دانلود قرارداد با خطا مواجه شد: ' . $e->getMessage());
            return redirect()->route('medical-center.contracts.view', $contract)
                ->with('error', 'خطایی در دانلود قرارداد رخ داد. لطفا مجددا تلاش کنید.');
        }
    }
    
    /**
     * Download a signed contract file.
     *
     * @param  int  $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadSignedContract($id)
    {
        try {
            $user = Auth::user();
            $contract = Contract::findOrFail($id);
            
            // Check if this contract belongs to the user's medical center
            if ($contract->medical_center_id != $user->medical_center_id) {
                return redirect()->route('contracts.index')
                    ->with('error', 'شما به این قرارداد دسترسی ندارید.');
            }
            
            // Check if signed file exists
            if (!$contract->signed_file_path || !Storage::disk('private')->exists($contract->signed_file_path)) {
                return redirect()->route('contracts.view', ['id' => $contract->id])
                    ->with('error', 'فایل قرارداد امضا شده یافت نشد.');
            }
            
            // Log activity
            Log::info('دانلود فایل قرارداد امضا شده', ['user_id' => $user->id, 'contract_id' => $contract->id]);
                
            return Storage::disk('private')->download(
                $contract->signed_file_path,
                $contract->signed_original_filename ?: 'قرارداد_امضا_شده.pdf'
            );
        } catch (\Exception $e) {
            Log::error('دانلود قرارداد امضا شده با خطا مواجه شد: ' . $e->getMessage());
            return redirect()->route('contracts.view', ['id' => $id])
                ->with('error', 'خطایی در دانلود قرارداد امضا شده رخ داد. لطفا مجددا تلاش کنید.');
        }
    }
}
