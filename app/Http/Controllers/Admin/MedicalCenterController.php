<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MedicalCenter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MedicalCenterController extends Controller
{
    /**
     * نمایش لیست مراکز درمانی
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $medicalCenters = MedicalCenter::latest()->paginate(10);
            
            Log::info('لیست مراکز درمانی با موفقیت بارگذاری شد', [
                'user_id' => auth()->id() ?? 'مهمان',
                'count' => $medicalCenters->count(),
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            return view('admin.medical-centers.index', compact('medicalCenters'));
        } catch (\Exception $e) {
            Log::error('خطا در بارگذاری لیست مراکز درمانی', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            return redirect()->back()->with('error', 'خطا در بارگذاری لیست مراکز درمانی. لطفاً مجدداً تلاش کنید.');
        }
    }

    /**
     * نمایش فرم ایجاد مرکز درمانی جدید
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.medical-centers.create');
    }

    /**
     * ذخیره مرکز درمانی جدید در دیتابیس
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'center_id' => 'required|string|max:20|unique:medical_centers',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string|max:500',
                'manager' => 'nullable|string|max:255',
            ], [
                'name.required' => 'نام مرکز درمانی الزامی است.',
                'center_id.required' => 'کد مرکز الزامی است.',
                'center_id.unique' => 'کد مرکز تکراری است.',
                'phone.required' => 'شماره تلفن الزامی است.',
                'email.email' => 'فرمت ایمیل صحیح نیست.',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            $medicalCenter = new MedicalCenter();
            $medicalCenter->name = $request->input('name');
            $medicalCenter->center_id = $request->input('center_id');
            $medicalCenter->phone = $request->input('phone');
            $medicalCenter->email = $request->input('email');
            $medicalCenter->address = $request->input('address');
            $medicalCenter->manager = $request->input('manager');
            $medicalCenter->save();
            
            Log::info('مرکز درمانی جدید با موفقیت ایجاد شد', [
                'user_id' => auth()->id() ?? 'مهمان',
                'center_id' => $medicalCenter->id,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            return redirect()->route('admin.medical-centers.index')
                ->with('success', 'مرکز درمانی با موفقیت ایجاد شد.');
        } catch (\Exception $e) {
            Log::error('خطا در ایجاد مرکز درمانی جدید', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            return redirect()->back()
                ->with('error', 'خطا در ایجاد مرکز درمانی. لطفاً مجدداً تلاش کنید.')
                ->withInput();
        }
    }

    /**
     * نمایش اطلاعات یک مرکز درمانی
     *
     * @param  \App\Models\MedicalCenter  $medicalCenter
     * @return \Illuminate\View\View
     */
    public function show(MedicalCenter $medicalCenter)
    {
        try {
            Log::info('نمایش جزئیات مرکز درمانی', [
                'user_id' => auth()->id() ?? 'مهمان',
                'center_id' => $medicalCenter->id,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            return view('admin.medical-centers.show', compact('medicalCenter'));
        } catch (\Exception $e) {
            Log::error('خطا در نمایش جزئیات مرکز درمانی', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'center_id' => $medicalCenter->id ?? 'نامشخص',
                'user_id' => auth()->id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            return redirect()->back()->with('error', 'خطا در نمایش اطلاعات مرکز درمانی. لطفاً مجدداً تلاش کنید.');
        }
    }

    /**
     * نمایش فرم ویرایش مرکز درمانی
     *
     * @param  \App\Models\MedicalCenter  $medicalCenter
     * @return \Illuminate\View\View
     */
    public function edit(MedicalCenter $medicalCenter)
    {
        return view('admin.medical-centers.edit', compact('medicalCenter'));
    }

    /**
     * بروزرسانی اطلاعات مرکز درمانی
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MedicalCenter  $medicalCenter
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, MedicalCenter $medicalCenter)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'center_id' => 'required|string|max:20|unique:medical_centers,center_id,' . $medicalCenter->id,
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string|max:500',
                'manager' => 'nullable|string|max:255',
            ], [
                'name.required' => 'نام مرکز درمانی الزامی است.',
                'center_id.required' => 'کد مرکز الزامی است.',
                'center_id.unique' => 'کد مرکز تکراری است.',
                'phone.required' => 'شماره تلفن الزامی است.',
                'email.email' => 'فرمت ایمیل صحیح نیست.',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            $medicalCenter->name = $request->input('name');
            $medicalCenter->center_id = $request->input('center_id');
            $medicalCenter->phone = $request->input('phone');
            $medicalCenter->email = $request->input('email');
            $medicalCenter->address = $request->input('address');
            $medicalCenter->manager = $request->input('manager');
            $medicalCenter->save();
            
            Log::info('اطلاعات مرکز درمانی با موفقیت بروزرسانی شد', [
                'user_id' => auth()->id() ?? 'مهمان',
                'center_id' => $medicalCenter->id,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            return redirect()->route('admin.medical-centers.index')
                ->with('success', 'مرکز درمانی با موفقیت بروزرسانی شد.');
        } catch (\Exception $e) {
            Log::error('خطا در بروزرسانی مرکز درمانی', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'center_id' => $medicalCenter->id ?? 'نامشخص',
                'user_id' => auth()->id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            return redirect()->back()
                ->with('error', 'خطا در بروزرسانی مرکز درمانی. لطفاً مجدداً تلاش کنید.')
                ->withInput();
        }
    }

    /**
     * حذف مرکز درمانی
     *
     * @param  \App\Models\MedicalCenter  $medicalCenter
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(MedicalCenter $medicalCenter)
    {
        try {
            $centerId = $medicalCenter->id;
            $centerName = $medicalCenter->name;
            
            // بررسی وابستگی‌ها قبل از حذف
            if ($medicalCenter->contracts()->count() > 0) {
                Log::warning('تلاش ناموفق برای حذف مرکز درمانی به دلیل وجود قرارداد وابسته', [
                    'user_id' => auth()->id() ?? 'مهمان',
                    'center_id' => $centerId,
                    'center_name' => $centerName,
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ]);
                
                return redirect()->back()
                    ->with('error', 'امکان حذف مرکز درمانی وجود ندارد. ابتدا قراردادهای وابسته را حذف کنید.');
            }
            
            $medicalCenter->delete();
            
            Log::info('مرکز درمانی با موفقیت حذف شد', [
                'user_id' => auth()->id() ?? 'مهمان',
                'center_id' => $centerId,
                'center_name' => $centerName,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            return redirect()->route('admin.medical-centers.index')
                ->with('success', 'مرکز درمانی با موفقیت حذف شد.');
        } catch (\Exception $e) {
            Log::error('خطا در حذف مرکز درمانی', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'center_id' => $medicalCenter->id ?? 'نامشخص',
                'center_name' => $medicalCenter->name ?? 'نامشخص',
                'user_id' => auth()->id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            return redirect()->back()
                ->with('error', 'خطا در حذف مرکز درمانی. لطفاً مجدداً تلاش کنید.');
        }
    }
}
