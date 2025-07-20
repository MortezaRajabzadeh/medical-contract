<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\MedicalCenter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Morilog\Jalali\Jalalian;

class ContractController extends Controller
{
    /**
     * تبدیل اعداد فارسی به انگلیسی
     *
     * @param string $string
     * @return string
     */
    protected function convertPersianNumbersToEnglish($string)
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($persian, $english, $string);
    }
    // وضعیت‌های مختلف قرارداد
    const STATUS_DRAFT = 'draft'; // پیش‌نویس
    const STATUS_PENDING = 'pending'; // در انتظار امضا
    const STATUS_UPLOADED = 'uploaded'; // آپلود شده توسط مرکز درمانی
    const STATUS_APPROVED = 'approved'; // تأیید شده
    const STATUS_REJECTED = 'rejected'; // رد شده
    const STATUS_ACTIVE = 'active'; // فعال
    const STATUS_EXPIRED = 'expired'; // منقضی شده
    const STATUS_TERMINATED = 'terminated'; // خاتمه یافته

    // دریافت لیست وضعیت‌ها برای فرم
    private function getStatusList()
    {
        return [
            self::STATUS_DRAFT => 'پیش‌نویس',
            self::STATUS_PENDING => 'در انتظار امضا',
            self::STATUS_UPLOADED => 'آپلود شده توسط مرکز',
            self::STATUS_APPROVED => 'تأیید شده',
            self::STATUS_REJECTED => 'رد شده',
            self::STATUS_ACTIVE => 'فعال',
            self::STATUS_EXPIRED => 'منقضی شده',
            self::STATUS_TERMINATED => 'خاتمه یافته',
        ];
    }
    /**
     * نمایش لیست قراردادها
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            $query = Contract::with(['medicalCenter', 'viewedBy' => function($query) {
                // فقط بارگذاری وضعیت دیده شدن توسط کاربر فعلی
                if (Auth::check()) {
                    $query->where('user_id', Auth::id());
                }
            }]);

            // اعمال فیلتر وضعیت
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // اعمال فیلتر مرکز درمانی
            if ($request->has('medical_center_id') && $request->medical_center_id) {
                $query->where('medical_center_id', $request->medical_center_id);
            }

            // اعمال فیلتر جستجو
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('contract_number', 'like', "%{$search}%")
                      ->orWhere('title', 'like', "%{$search}%");
                });
            }

            $contracts = $query->latest()->paginate(10);
            $medicalCenters = MedicalCenter::pluck('name', 'id');



            return view('admin.contracts.index', compact('contracts', 'medicalCenters'));
        } catch (\Exception $e) {

            return redirect()->route('admin.dashboard')->with('error', 'خطا در بارگذاری لیست قراردادها. لطفاً مجدداً تلاش کنید.');
        }
    }

    /**
     * نمایش فرم ایجاد قرارداد جدید
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            $medicalCenters = MedicalCenter::orderBy('name')->pluck('name', 'id');
            $statusList = $this->getStatusList();

            Log::info('فرم ایجاد قرارداد جدید با موفقیت بارگذاری شد', [
                'user_id' => auth()->id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

            return view('admin.contracts.create', compact('medicalCenters', 'statusList'));
        } catch (\Exception $e) {
            Log::error('خطا در بارگذاری فرم ایجاد قرارداد جدید', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

            return redirect()->route('admin.contracts.index')->with('error', 'خطا در بارگذاری فرم ایجاد قرارداد جدید. لطفاً مجدداً تلاش کنید.');
        }
    }

    /**
     * ذخیره قرارداد جدید در دیتابیس
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    /**
     * نمایش فرم ویرایش قرارداد
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    /**
     * نمایش جزئیات قرارداد
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $contract = Contract::with('medicalCenter')->findOrFail($id);
            $statusList = $this->getStatusList();
            
            // قرارداد را به عنوان دیده شده علامت بزن
            $this->markAsViewed($contract);
            
            return view('admin.contracts.show', compact('contract', 'statusList'));
        } catch (\Exception $e) {


            return redirect()->route('admin.contracts.index')
                ->with('error', 'خطا در نمایش جزئیات قرارداد: ' . $e->getMessage());
        }
    }

    /**
     * نمایش فرم ویرایش قرارداد
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            $contract = Contract::findOrFail($id);
            $statusList = $this->getStatusList();
            $medicalCenters = MedicalCenter::pluck('name', 'id');
            
            // قرارداد را به عنوان دیده شده علامت بزن
            $this->markAsViewed($contract);

            Log::info('فرم ویرایش قرارداد بارگذاری شد', [
                'contract_id' => $id,
                'contract_number' => $contract->contract_number,
                'user_id' => Auth::id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

            return view('admin.contracts.edit', compact('contract', 'medicalCenters', 'statusList'));
        } catch (\Exception $e) {
            Log::error('خطا در بارگذاری فرم ویرایش قرارداد', [
                'contract_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

            return redirect()->route('admin.contracts.index')
                ->with('error', 'خطا در بارگذاری فرم ویرایش قرارداد: ' . $e->getMessage());
        }
    }

    /**
     * به‌روزرسانی قرارداد موجود در دیتابیس
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    /**
     * نشانه‌گذاری قرارداد به عنوان دیده شده توسط کاربر فعلی
     *
     * @param  int  $id
     * @return void
     */
    protected function markAsViewed(Contract $contract)
    {
        try {
            if (Auth::check()) {
                // اگر کاربر قبلاً این قرارداد را مشاهده نکرده باشد، ان را به عنوان دیده شده علامت بزن
                if (!$contract->viewedBy()->where('user_id', Auth::id())->exists()) {
                    $contract->viewedBy()->attach(Auth::id(), [
                        'viewed_at' => now()
                    ]);
                    
                    Log::info('قرارداد به عنوان دیده شده علامت گذاری شد', [
                        'contract_id' => $contract->id,
                        'contract_number' => $contract->contract_number,
                        'user_id' => Auth::id(),
                        'timestamp' => now()->format('Y-m-d H:i:s')
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('خطا در نشانه‌گذاری قرارداد به عنوان دیده شده', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * دانلود فایل قرارداد
     *
     * @param  int  $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($id)
    {
        try {
            $contract = Contract::findOrFail($id);
            
            if (!$contract->file_path) {
                Log::error('تلاش برای دانلود فایل قراردادی که فایل ندارد', [
                    'contract_id' => $id,
                    'contract_number' => $contract->contract_number,
                    'user_id' => Auth::id() ?? 'مهمان',
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ]);
                
                return redirect()->back()
                    ->with('error', 'این قرارداد فایلی برای دانلود ندارد.');
            }
            
            $filePath = storage_path('app/public/' . $contract->file_path);
            
            if (!file_exists($filePath)) {
                Log::error('فایل قرارداد در مسیر مشخص شده یافت نشد', [
                    'contract_id' => $id,
                    'file_path' => $contract->file_path,
                    'full_path' => $filePath,
                    'user_id' => Auth::id() ?? 'مهمان',
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ]);
                
                return redirect()->back()
                    ->with('error', 'فایل درخواستی در سرور یافت نشد. لطفاً با پشتیبانی تماس بگیرید.');
            }
            
            // استخراج نام اصلی فایل از مسیر
            $originalName = basename($contract->file_path);
            
            // استخراج پسوند فایل
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            
            // ایجاد نام دانلود با استفاده از شماره قرارداد
            $downloadName = str_replace(' ', '_', "قرارداد_{$contract->contract_number}.{$extension}");
            
            Log::info('دانلود فایل قرارداد', [
                'contract_id' => $id,
                'contract_number' => $contract->contract_number,
                'file_path' => $contract->file_path,
                'download_name' => $downloadName,
                'user_id' => Auth::id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            return response()->download($filePath, $downloadName);
            
        } catch (\Exception $e) {
            Log::error('خطا در دانلود فایل قرارداد', [
                'contract_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            return redirect()->back()
                ->with('error', 'خطا در دانلود فایل: ' . $e->getMessage());
        }
    }

    /**
     * به‌روزرسانی قرارداد موجود در دیتابیس
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $contract = Contract::findOrFail($id);
            
            // دریافت و پردازش داده‌های ارسالی از فرم
            $requestData = $request->all();
            
            // تبدیل اعداد فارسی به انگلیسی در مقادیر تاریخ
            $dateFields = ['start_date', 'end_date'];
            foreach ($dateFields as $dateField) {
                if (!empty($requestData[$dateField])) {
                    $requestData[$dateField] = $this->convertPersianNumbersToEnglish($requestData[$dateField]);
                    
                    Log::info("تبدیل اعداد فارسی تاریخ به اعداد انگلیسی برای فیلد {$dateField}", [
                        'before' => $request->input($dateField),
                        'after' => $requestData[$dateField],
                        'value' => $requestData[$dateField],
                        'user_id' => Auth::id() ?? 'مهمان',
                        'timestamp' => now()->format('Y-m-d H:i:s')
                    ]);
                }
            }
            
            // تبدیل تاریخ شروع
            if (!empty($requestData['start_date'])) {
                try {
                    $startDateJalali = $requestData['start_date'];
                    $requestData['start_date'] = Jalalian::fromFormat('Y/m/d', $startDateJalali)->toCarbon()->format('Y-m-d');
                    
                    Log::info('تبدیل موفق تاریخ شروع جلالی به میلادی', [
                        'jalali_date' => $startDateJalali,
                        'gregorian_date' => $requestData['start_date'],
                        'user_id' => Auth::id() ?? 'مهمان',
                        'timestamp' => now()->format('Y-m-d H:i:s')
                    ]);
                    
                } catch (\Exception $e) {
                    Log::warning('خطا در تبدیل تاریخ شروع جلالی به میلادی', [
                        'date' => $requestData['start_date'],
                        'error' => $e->getMessage(),
                        'user_id' => Auth::id() ?? 'مهمان',
                        'timestamp' => now()->format('Y-m-d H:i:s')
                    ]);
                    // حفظ تاریخ اصلی برای اعتبارسنجی
                }
            }
            
            // تبدیل تاریخ پایان
            if (!empty($requestData['end_date'])) {
                try {
                    $endDateJalali = $requestData['end_date'];
                    $requestData['end_date'] = Jalalian::fromFormat('Y/m/d', $endDateJalali)->toCarbon()->format('Y-m-d');
                    
                    Log::info('تبدیل موفق تاریخ پایان جلالی به میلادی', [
                        'jalali_date' => $endDateJalali,
                        'gregorian_date' => $requestData['end_date'],
                        'user_id' => Auth::id() ?? 'مهمان',
                        'timestamp' => now()->format('Y-m-d H:i:s')
                    ]);
                    
                } catch (\Exception $e) {
                    Log::warning('خطا در تبدیل تاریخ پایان جلالی به میلادی', [
                        'date' => $requestData['end_date'],
                        'error' => $e->getMessage(),
                        'user_id' => Auth::id() ?? 'مهمان',
                        'timestamp' => now()->format('Y-m-d H:i:s')
                    ]);
                    // حفظ تاریخ اصلی برای اعتبارسنجی
                }
            }
            
            // اعتبارسنجی داده‌ها
            $validator = Validator::make($requestData, [
                'medical_center_id' => 'required|exists:medical_centers,id',
                'contract_number' => 'required|string|max:50|unique:contracts,contract_number,' . $contract->id,
                'title' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'status' => 'required|in:' . implode(',', array_keys($this->getStatusList())),
                'description' => 'nullable|string|max:1000',
            ], [
                'medical_center_id.required' => 'انتخاب مرکز درمانی الزامی است.',
                'medical_center_id.exists' => 'مرکز درمانی انتخاب شده معتبر نیست.',
                'contract_number.required' => 'شماره قرارداد الزامی است.',
                'contract_number.unique' => 'این شماره قرارداد قبلاً ثبت شده است.',
                'title.required' => 'عنوان قرارداد الزامی است.',
                'start_date.required' => 'تاریخ شروع قرارداد الزامی است.',
                'start_date.date' => 'تاریخ شروع قرارداد باید یک تاریخ معتبر باشد.',
                'end_date.required' => 'تاریخ پایان قرارداد الزامی است.',
                'end_date.date' => 'تاریخ پایان قرارداد باید یک تاریخ معتبر باشد.',
                'end_date.after_or_equal' => 'تاریخ پایان باید بعد از یا برابر با تاریخ شروع باشد.',
                'status.required' => 'وضعیت قرارداد الزامی است.',
                'status.in' => 'وضعیت قرارداد انتخاب شده معتبر نیست.',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // به‌روزرسانی اطلاعات قرارداد
            $contract->medical_center_id = $requestData['medical_center_id'];
            $contract->contract_number = $requestData['contract_number'];
            $contract->title = $requestData['title'];
            $contract->start_date = $requestData['start_date'];
            $contract->end_date = $requestData['end_date'];
            $contract->status = $requestData['status'];
            $contract->description = $requestData['description'] ?? null;
            
            // آپلود فایل قرارداد جدید اگر وجود داشته باشد
            if ($request->hasFile('contract_file')) {
                try {
                    $file = $request->file('contract_file');
                    
                    // بررسی اعتبار فایل
                    if (!$file->isValid()) {
                        Log::warning('فایل آپلود شده معتبر نیست', [
                            'original_name' => $file->getClientOriginalName(),
                            'error' => $file->getError(),
                            'user_id' => Auth::id() ?? 'مهمان'
                        ]);
                        
                        return redirect()->back()
                            ->with('error', 'فایل آپلود شده معتبر نیست')
                            ->withInput();
                    }
                    
                    // تهیه نام فایل امن
                    $safeContractNumber = preg_replace('/[^A-Za-z0-9\-_]/', '', $contract->contract_number);
                    if (empty($safeContractNumber)) {
                        $safeContractNumber = 'contract';
                    }
                    
                    $timestamp = time();
                    $extension = $file->getClientOriginalExtension() ?: 'pdf';
                    $fileName = $safeContractNumber . '_' . $timestamp . '.' . $extension;
                    
                    // آپلود مستقیم فایل به جای استفاده از storeAs
                    $path = storage_path('app/public/contracts');
                    
                    // اطمینان از وجود دایرکتوری
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    
                    // حذف فایل قبلی در صورت وجود
                    if ($contract->file_path && Storage::disk('public')->exists($contract->file_path)) {
                        Storage::disk('public')->delete($contract->file_path);
                        Log::info('فایل قبلی قرارداد حذف شد', [
                            'file_path' => $contract->file_path,
                            'contract_id' => $contract->id,
                            'user_id' => Auth::id() ?? 'مهمان'
                        ]);
                    }
                    
                    $fullPath = $path . '/' . $fileName;
                    
                    // آپلود فایل با مدیریت خطای دقیق
                    if ($file->move($path, $fileName)) {
                        // ذخیره مسیر نسبی برای دیتابیس
                        $contract->file_path = 'contracts/' . $fileName;
                        
                        Log::info('فایل قرارداد با موفقیت آپلود شد', [
                            'file_name' => $fileName,
                            'file_path' => $contract->file_path,
                            'contract_number' => $contract->contract_number,
                            'user_id' => Auth::id() ?? 'مهمان'
                        ]);
                    } else {
                        Log::error('خطا در آپلود فایل قرارداد', [
                            'file_name' => $fileName,
                            'user_id' => Auth::id() ?? 'مهمان'
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('خطا در آپلود فایل قرارداد', [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'contract_number' => $contract->contract_number,
                        'user_id' => Auth::id() ?? 'مهمان'
                    ]);
                }
            }
            
            // ذخیره تغییرات
            $contract->save();
            
            Log::info('قرارداد با موفقیت به‌روزرسانی شد', [
                'contract_id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'user_id' => Auth::id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            return redirect()->route('admin.contracts.index')
                ->with('success', 'قرارداد با موفقیت به‌روزرسانی شد.');
        } catch (\Exception $e) {
            Log::error('خطا در به‌روزرسانی قرارداد', [
                'contract_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            return redirect()->back()
                ->with('error', 'خطا در به‌روزرسانی قرارداد: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ذخیره قرارداد جدید در دیتابیس
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            // تبدیل تاریخ‌های جلالی به میلادی
            $requestData = $request->all();

            // ابتدا همه فیلدهای تاریخ را از فارسی به انگلیسی تبدیل می‌کنیم
            foreach(['start_date', 'end_date'] as $dateField) {
                if (!empty($requestData[$dateField])) {
                    $requestData[$dateField] = $this->convertPersianNumbersToEnglish($requestData[$dateField]);

                    // لاگ برای ردیابی
                    Log::info("تبدیل اعداد فارسی به انگلیسی برای {$dateField}", [
                        'field' => $dateField,
                        'value' => $requestData[$dateField],
                        'user_id' => Auth::id() ?? 'مهمان',
                        'timestamp' => now()->format('Y-m-d H:i:s')
                    ]);
                }
            }

            // تبدیل تاریخ شروع
            if (!empty($requestData['start_date'])) {
                try {
                    $startDateJalali = $requestData['start_date'];
                    $requestData['start_date'] = Jalalian::fromFormat('Y/m/d', $startDateJalali)->toCarbon()->format('Y-m-d');

                    Log::info('تبدیل موفق تاریخ شروع جلالی به میلادی', [
                        'jalali_date' => $startDateJalali,
                        'gregorian_date' => $requestData['start_date'],
                        'user_id' => Auth::id() ?? 'مهمان',
                        'timestamp' => now()->format('Y-m-d H:i:s')
                    ]);

                } catch (\Exception $e) {
                    Log::warning('خطا در تبدیل تاریخ شروع جلالی به میلادی', [
                        'date' => $requestData['start_date'],
                        'error' => $e->getMessage(),
                        'user_id' => Auth::id() ?? 'مهمان',
                        'timestamp' => now()->format('Y-m-d H:i:s')
                    ]);
                    // حفظ تاریخ اصلی برای اعتبارسنجی
                }
            }

            // تبدیل تاریخ پایان
            if (!empty($requestData['end_date'])) {
                try {
                    $endDateJalali = $requestData['end_date'];
                    $requestData['end_date'] = Jalalian::fromFormat('Y/m/d', $endDateJalali)->toCarbon()->format('Y-m-d');

                    Log::info('تبدیل موفق تاریخ پایان جلالی به میلادی', [
                        'jalali_date' => $endDateJalali,
                        'gregorian_date' => $requestData['end_date'],
                        'user_id' => Auth::id() ?? 'مهمان',
                        'timestamp' => now()->format('Y-m-d H:i:s')
                    ]);

                } catch (\Exception $e) {
                    Log::warning('خطا در تبدیل تاریخ پایان جلالی به میلادی', [
                        'date' => $requestData['end_date'],
                        'error' => $e->getMessage(),
                        'user_id' => Auth::id() ?? 'مهمان',
                        'timestamp' => now()->format('Y-m-d H:i:s')
                    ]);
                    // حفظ تاریخ اصلی برای اعتبارسنجی
                }
            }

            $validator = Validator::make($requestData, [
                'medical_center_id' => 'required|exists:medical_centers,id',
                'contract_number' => 'required|string|max:50|unique:contracts',
                'title' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'status' => 'required|in:' . implode(',', array_keys($this->getStatusList())),
                // 'description' => 'nullable|string|max:1000',
            ], [
                'medical_center_id.required' => 'انتخاب مرکز درمانی الزامی است.',
                'medical_center_id.exists' => 'مرکز درمانی انتخاب شده معتبر نیست.',
                'contract_number.required' => 'شماره قرارداد الزامی است.',
                'contract_number.unique' => 'این شماره قرارداد قبلاً ثبت شده است.',
                'title.required' => 'عنوان قرارداد الزامی است.',
                'start_date.required' => 'تاریخ شروع الزامی است.',
                'start_date.date' => 'فرمت تاریخ شروع نامعتبر است.',
                'end_date.required' => 'تاریخ پایان الزامی است.',
                'end_date.date' => 'فرمت تاریخ پایان نامعتبر است.',
                'end_date.after_or_equal' => 'تاریخ پایان باید بعد از تاریخ شروع باشد.',
                'status.required' => 'وضعیت قرارداد الزامی است.',
                'status.in' => 'وضعیت قرارداد معتبر نیست.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $contract = new Contract();
            $contract->medical_center_id = $requestData['medical_center_id'];
            $contract->contract_number = $requestData['contract_number'];
            $contract->title = $requestData['title'];
            $contract->start_date = $requestData['start_date'];
            $contract->end_date = $requestData['end_date'];
            // فیلد signed_date حذف شد
            // فیلد amount حذف شد
            $contract->status = $requestData['status'];
            $contract->description = $requestData['description'] ?? null;
            $contract->vendor_name = 'نامشخص'; // مقدار پیش‌فرض برای فیلد vendor_name
            $contract->created_by = Auth::id();

            // آپلود فایل قرارداد اگر وجود داشته باشد
            $contract->file_path = null; // مقدار پیش‌فرض

            if ($request->hasFile('contract_file')) {
                try {
                    $file = $request->file('contract_file');

                    // بررسی اعتبار فایل
                    if (!$file->isValid()) {
                        Log::warning('فایل آپلود شده معتبر نیست', [
                            'original_name' => $file->getClientOriginalName(),
                            'error' => $file->getError(),
                            'user_id' => Auth::id() ?? 'مهمان'
                        ]);
                        return; // خروج از آپلود فایل
                    }

                    // تهیه نام فایل امن
                    $safeContractNumber = preg_replace('/[^A-Za-z0-9\-_]/', '', $contract->contract_number);
                    if (empty($safeContractNumber)) {
                        $safeContractNumber = 'contract';
                    }

                    $timestamp = time();
                    $extension = $file->getClientOriginalExtension() ?: 'pdf';
                    $fileName = $safeContractNumber . '_' . $timestamp . '.' . $extension;

                    // آپلود مستقیم فایل به جای استفاده از storeAs
                    $path = storage_path('app/public/contracts');

                    // اطمینان از وجود دایرکتوری
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }

                    $fullPath = $path . '/' . $fileName;

                    // آپلود فایل با مدیریت خطای دقیق
                    if ($file->move($path, $fileName)) {
                        // ذخیره مسیر نسبی برای دیتابیس
                        $contract->file_path = 'contracts/' . $fileName;

                        Log::info('فایل قرارداد با موفقیت آپلود شد', [
                            'file_name' => $fileName,
                            'file_path' => $contract->file_path,
                            'contract_number' => $contract->contract_number,
                            'user_id' => Auth::id() ?? 'مهمان'
                        ]);
                    } else {
                        Log::error('خطا در آپلود فایل قرارداد', [
                            'file_name' => $fileName,
                            'user_id' => Auth::id() ?? 'مهمان'
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('خطا در آپلود فایل قرارداد', [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'contract_number' => $contract->contract_number,
                        'user_id' => Auth::id() ?? 'مهمان'
                    ]);
                }
            } else {
                Log::info('فایلی برای آپلود ارسال نشده است', [
                    'contract_number' => $contract->contract_number,
                    'user_id' => Auth::id() ?? 'مهمان'
                ]);
            }

            $contract->save();

            Log::info('قرارداد جدید با موفقیت ایجاد شد', [
                'contract_id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'user_id' => Auth::id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

            return redirect()->route('admin.contracts.index')
                ->with('success', 'قرارداد جدید با موفقیت ایجاد شد.');
        } catch (\Exception $e) {
            Log::error('خطا در ایجاد قرارداد جدید', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

            return redirect()->back()
                ->with('error', 'خطا در ایجاد قرارداد جدید: ' . $e->getMessage())
                ->withInput();
        }
    }
}
