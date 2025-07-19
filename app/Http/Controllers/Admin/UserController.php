<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * نمایش لیست کاربران
     */
    public function index()
    {
        try {
            $users = User::latest()->paginate(15);
            return view('admin.users.index', compact('users'));
        } catch (\Exception $e) {
            Log::error('خطا در نمایش لیست کاربران: ' . $e->getMessage(), [
                'error_trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'خطایی در نمایش لیست کاربران رخ داده است.');
        }
    }

    /**
     * نمایش فرم ایجاد کاربر جدید
     */
    public function create()
    {
        try {
            return view('admin.users.create');
        } catch (\Exception $e) {
            Log::error('خطا در نمایش فرم ایجاد کاربر: ' . $e->getMessage(), [
                'error_trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'خطایی در نمایش فرم ایجاد کاربر رخ داده است.');
        }
    }

    /**
     * ذخیره کاربر جدید
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'mobile' => 'nullable|string|max:15|unique:users',
                'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
                'user_type' => 'required|string|in:admin,medical_staff,administrative_staff',
                'is_active' => 'required|boolean',
            ], [
                'name.required' => 'نام و نام خانوادگی الزامی است.',
                'email.required' => 'آدرس ایمیل الزامی است.',
                'email.email' => 'فرمت ایمیل صحیح نیست.',
                'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
                'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
                'password.required' => 'رمز عبور الزامی است.',
                'password.confirmed' => 'رمز عبور و تکرار آن مطابقت ندارند.',
                'password.min' => 'رمز عبور باید حداقل 8 کاراکتر باشد.',
                'user_type.required' => 'انتخاب نقش کاربری الزامی است.',
                'user_type.in' => 'نقش کاربری انتخاب شده معتبر نیست.',
                'is_active.required' => 'وضعیت کاربر الزامی است.',
            ]);

            if ($validator->fails()) {
                return redirect()->route('admin.users.create')
                    ->withErrors($validator)
                    ->withInput();
            }
            
            $user = new User();
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->mobile = $request->input('mobile');
            $user->password = Hash::make($request->input('password'));
            $user->user_type = $request->input('user_type');
            $user->is_active = $request->input('is_active');
            $user->save();
            
            // اگر کاربر مدیر سیستم است، نقش admin را به او اختصاص دهید
            if ($user->user_type === 'admin') {
                $user->assignRole('admin');
            }
            
            Log::info('کاربر جدید با موفقیت ایجاد شد.', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_type' => $user->user_type,
            ]);
            
            return redirect()->route('admin.users.index')
                ->with('success', 'کاربر جدید با موفقیت ایجاد شد.');
        } catch (\Exception $e) {
            Log::error('خطا در ذخیره کاربر جدید: ' . $e->getMessage(), [
                'error_trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);
            return back()->withInput()->with('error', 'خطایی در ایجاد کاربر رخ داده است.');
        }
    }

    /**
     * نمایش اطلاعات کاربر
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return view('admin.users.show', compact('user'));
        } catch (\Exception $e) {
            Log::error('خطا در نمایش اطلاعات کاربر: ' . $e->getMessage(), [
                'error_trace' => $e->getTraceAsString(),
                'user_id' => $id
            ]);
            return back()->with('error', 'کاربر مورد نظر یافت نشد.');
        }
    }

    /**
     * نمایش فرم ویرایش کاربر
     */
    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);
            return view('admin.users.edit', compact('user'));
        } catch (\Exception $e) {
            Log::error('خطا در نمایش فرم ویرایش کاربر: ' . $e->getMessage(), [
                'error_trace' => $e->getTraceAsString(),
                'user_id' => $id
            ]);
            return back()->with('error', 'کاربر مورد نظر یافت نشد.');
        }
    }

    /**
     * بروزرسانی اطلاعات کاربر
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'mobile' => 'nullable|string|max:15|unique:users,mobile,' . $user->id,
                'user_type' => 'required|string|in:admin,medical_staff,administrative_staff',
                'is_active' => 'required|boolean',
            ];
            
            // اگر رمز عبور ارسال شده باشد، قوانین اعتبارسنجی آن را اضافه کنید
            if ($request->filled('password')) {
                $rules['password'] = ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()];
            }
            
            $messages = [
                'name.required' => 'نام و نام خانوادگی الزامی است.',
                'email.required' => 'آدرس ایمیل الزامی است.',
                'email.email' => 'فرمت ایمیل صحیح نیست.',
                'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
                'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
                'user_type.required' => 'انتخاب نقش کاربری الزامی است.',
                'user_type.in' => 'نقش کاربری انتخاب شده معتبر نیست.',
                'is_active.required' => 'وضعیت کاربر الزامی است.',
            ];
            
            if ($request->filled('password')) {
                $messages['password.required'] = 'رمز عبور الزامی است.';
                $messages['password.confirmed'] = 'رمز عبور و تکرار آن مطابقت ندارند.';
                $messages['password.min'] = 'رمز عبور باید حداقل 8 کاراکتر باشد.';
            }
            
            $validator = Validator::make($request->all(), $rules, $messages);
            
            if ($validator->fails()) {
                return redirect()->route('admin.users.edit', $user->id)
                    ->withErrors($validator)
                    ->withInput();
            }
            
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->mobile = $request->input('mobile');
            
            if ($request->filled('password')) {
                $user->password = Hash::make($request->input('password'));
            }
            
            // اگر نوع کاربر تغییر کرده باشد، نقش‌های آن را بروزرسانی کنید
            if ($user->user_type !== $request->input('user_type')) {
                // نقش‌های قبلی را حذف کنید
                $user->roles()->detach();
                
                // اگر کاربر مدیر سیستم است، نقش admin را به او اختصاص دهید
                if ($request->input('user_type') === 'admin') {
                    $user->assignRole('admin');
                }
            }
            
            $user->user_type = $request->input('user_type');
            $user->is_active = $request->input('is_active');
            $user->save();
            
            Log::info('اطلاعات کاربر با موفقیت بروزرسانی شد.', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_type' => $user->user_type,
            ]);
            
            return redirect()->route('admin.users.index')
                ->with('success', 'اطلاعات کاربر با موفقیت بروزرسانی شد.');
        } catch (\Exception $e) {
            Log::error('خطا در بروزرسانی اطلاعات کاربر: ' . $e->getMessage(), [
                'error_trace' => $e->getTraceAsString(),
                'user_id' => $id,
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);
            return back()->withInput()->with('error', 'خطایی در بروزرسانی اطلاعات کاربر رخ داده است.');
        }
    }

    /**
     * حذف کاربر
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // بررسی کنید که آیا کاربر جاری در حال حذف خود است یا خیر
            if (auth()->id() == $id) {
                return back()->with('error', 'شما نمی‌توانید حساب کاربری خود را حذف کنید.');
            }
            
            $user->delete();
            
            Log::info('کاربر با موفقیت حذف شد.', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_type' => $user->user_type,
            ]);
            
            return redirect()->route('admin.users.index')
                ->with('success', 'کاربر با موفقیت حذف شد.');
        } catch (\Exception $e) {
            Log::error('خطا در حذف کاربر: ' . $e->getMessage(), [
                'error_trace' => $e->getTraceAsString(),
                'user_id' => $id
            ]);
            return back()->with('error', 'خطایی در حذف کاربر رخ داده است.');
        }
    }
}
