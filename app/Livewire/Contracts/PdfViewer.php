<?php

namespace App\Livewire\Contracts;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Contract;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Spatie\Activitylog\Facades\CauserResolver;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\LogBatchActivity as activity;
use Exception;

/**
 * کلاس کمکی برای لاگ کردن با فرمت استاندارد
 */
class ContractLogger
{
    /**
     * لاگ کردن با فرمت استاندارد برای قابلیت ردیابی
     *
     * @param string $level سطح لاگ (info, error, warning, debug)
     * @param string $message پیام لاگ
     * @param array $context داده‌های اضافی
     * @return void
     */
    public static function log($level, $message, $context = [])
    {
        // افزودن اطلاعات ردیابی به لاگ
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = isset($trace[1]) ? $trace[1] : $trace[0];

        $contextWithTracing = array_merge($context, [
            'file' => $caller['file'] ?? null,
            'line' => $caller['line'] ?? null,
            'function' => $caller['function'] ?? null,
            'class' => $caller['class'] ?? null,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => auth()->id() ?? null,
            'medical_center_id' => auth()->user()->medical_center_id ?? null,
            'session_id' => session()->getId(),
            'request_id' => uniqid('req_'),
        ]);

        Log::$level($message, $contextWithTracing);
    }

    /**
     * لاگ اطلاعاتی
     *
     * @param string $message پیام لاگ
     * @param array $context داده‌های اضافی
     * @return void
     */
    public static function info($message, $context = [])
    {
        self::log('info', $message, $context);
    }

    /**
     * لاگ خطا
     *
     * @param string $message پیام لاگ
     * @param array $context داده‌های اضافی
     * @return void
     */
    public static function error($message, $context = [])
    {
        self::log('error', $message, $context);
    }

    /**
     * لاگ خطا از نوع Exception
     *
     * @param Exception $e استثنا
     * @param array $context داده‌های اضافی
     * @return void
     */
    public static function exception(Exception $e, $context = [])
    {
        $exceptionContext = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];

        self::log('error', 'Exception: ' . $e->getMessage(), array_merge($context, $exceptionContext));
    }
}

class PdfViewer extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public Contract $contract;
    public $showViewer = false;
    public $signedContractFile = null;
    public $showNotification = false;
    public $notificationType = '';
    public $notificationMessage = '';

    /**
     * متا برای مشخص کردن گوش دادن به رویداد closeViewer در Livewire 3
     */
    protected $listeners = [
        'closeViewer'
    ];

    public function mount($contractId)
    {
        try {
            // دریافت قرارداد از دیتابیس با استفاده از ID
            $this->contract = Contract::findOrFail($contractId);

            // بررسی مجوز دسترسی
            $this->authorize('view', $this->contract);

            // ثبت لاگ برای دیباگ با استفاده از لاگر جدید
            ContractLogger::info('دریافت موفق قرارداد در PdfViewer', [
                'contract_id' => $contractId,
                'component' => 'PdfViewer',
                'action' => 'mount',
            ]);
        } catch (\Exception $e) {
            // استفاده از لاگر جدید برای ثبت خطا
            ContractLogger::exception($e, [
                'contract_id' => $contractId,
                'component' => 'PdfViewer',
                'action' => 'mount',
            ]);
            throw $e;
        }
    }

    public function toggleViewer()
    {
        $this->showViewer = !$this->showViewer;

        // ارسال رویداد به Alpine.js برای همگام‌سازی
        $this->dispatch('showViewerChanged', $this->showViewer);

        if ($this->showViewer) {
            // به جای activity از لاگ استفاده می‌کنیم
            Log::info('PDF قرارداد مشاهده شد', [
                'user_id' => auth()->id(),
                'contract_id' => $this->contract->id,
                'action' => 'view_contract_pdf'
            ]);

            // لاگ برای دیباگ با استفاده از لاگر جدید
            ContractLogger::info('نمایشگر PDF باز شد', [
                'contract_id' => $this->contract->id,
                'user_id' => auth()->id(),
                'showViewer' => $this->showViewer,
            ]);
        }
    }

    /**
     * بستن نمایشگر PDF برای استفاده از طریق dispatchTo در Livewire 3
     */
    public function closeViewer()
    {
        if ($this->showViewer) {
            $this->showViewer = false;
            
            // ارسال رویداد به Alpine.js برای همگام‌سازی
            $this->dispatch('showViewerChanged', $this->showViewer);
            
            // استفاده از لاگر جدید برای دیباگ بهتر
            ContractLogger::info('نمایشگر PDF بسته شد', [
                'contract_id' => $this->contract->id,
                'user_id' => auth()->id(),
                'showViewer' => $this->showViewer,
                'action' => 'closeViewer'
            ]);
        }
    }

    public function downloadPdf()
    {
        $this->authorize('download', $this->contract);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($this->contract)
            ->log('Downloaded contract PDF');

        return Storage::disk('private')->download(
            $this->contract->file_path,
            $this->contract->original_filename
        );
    }

    public function uploadSignedContract()
    {
        try {
            // ثبت لاگ قبل از بررسی مجوز
            \Log::info('شروع آپلود فایل امضا شده', [
                'contract_id' => $this->contract->id,
                'user_id' => auth()->id(),
                'user_roles' => auth()->user()->roles->pluck('name'),
                'user_medical_center_id' => auth()->user()->medical_center_id,
                'contract_medical_center_id' => $this->contract->medical_center_id
            ]);
            
            // بررسی مجوز دسترسی برای آپلود
            $canUpload = auth()->user()->can('upload', $this->contract);
            \Log::info('نتیجه بررسی مجوز آپلود', ['can_upload' => $canUpload]);
            
            if (!$canUpload) {
                \Log::error('خطای عدم دسترسی برای آپلود', [
                    'user_id' => auth()->id(),
                    'contract_id' => $this->contract->id
                ]);
                throw new \Illuminate\Auth\Access\AuthorizationException('شما مجاز به آپلود فایل امضا شده نیستید.');
            }
            
            // اعتبارسنجی فایل
            \Log::info('شروع اعتبارسنجی فایل', ['file' => $this->signedContractFile ? 'وجود دارد' : 'وجود ندارد']);
            
            $this->validate([
                'signedContractFile' => 'required|file|mimes:pdf|max:10240', // حداکثر 10 مگابایت
            ], [
                'signedContractFile.required' => 'لطفاً یک فایل PDF انتخاب کنید.',
                'signedContractFile.file' => 'فایل انتخاب شده معتبر نیست.',
                'signedContractFile.mimes' => 'فقط فایل‌های PDF مجاز هستند.',
                'signedContractFile.max' => 'حداکثر اندازه فایل 10 مگابایت است.'
            ]);
            
            \Log::info('اعتبارسنجی فایل با موفقیت انجام شد');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('خطای اعتبارسنجی فایل', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('خطای دسترسی یا عملیات', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        try {
            // مسیر ذخیره‌سازی فایل جدید
            $originalFilename = pathinfo($this->contract->original_filename, PATHINFO_FILENAME);
            $newFilename = $originalFilename . '_signed_' . now()->format('Ymd_His') . '.pdf';
            $signedFilePath = 'contracts/signed/' . $this->contract->id . '/' . $newFilename;

            // ذخیره فایل امضا شده
            $storedPath = $this->signedContractFile->storeAs(
                'private/contracts/signed/' . $this->contract->id,
                $newFilename,
                'public'
            );

            if (!$storedPath) {
                throw new \Exception('خطا در ذخیره‌سازی فایل امضا شده');
            }

            // به‌روزرسانی قرارداد
            $this->contract->signed_file_path = $signedFilePath;
            $this->contract->signed_date = now();
            $this->contract->status = 'SIGNED'; // تغییر وضعیت به امضا شده - با حروف بزرگ برای مطابقت با enum دیتابیس
            $this->contract->save();

            // ثبت لاگ اصلی برای آپلود موفق
            Log::info('قرارداد امضا شده آپلود شد', [
                'user_id' => Auth::id(),
                'contract_id' => $this->contract->id,
                'action' => 'upload_signed_contract',
                'status' => $this->contract->status
            ]);

            Log::info('آپلود موفق قرارداد امضا شده', [
                'contract_id' => $this->contract->id,
                'user_id' => auth()->id(),
                'medical_center_id' => auth()->user()->medical_center_id ?? null,
                'file_path' => $signedFilePath
            ]);

            // پاک کردن فایل موقت آپلود شده
            $this->reset(['signedContractFile']);

            // نمایش نوتیفیکیشن موفقیت با متغیرهای Livewire
            $this->showNotification = true;
            $this->notificationType = 'success';
            $this->notificationMessage = 'قرارداد امضا شده با موفقیت بارگذاری شد.';
            
            // راه‌اندازی تایمر برای مخفی کردن خودکار نوتیفیکیشن (با جاوااسکریپت)
            $this->dispatch('notification-timer');

        } catch (\Exception $e) {
            Log::error('خطا در آپلود قرارداد امضا شده', [
                'contract_id' => $this->contract->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'medical_center_id' => auth()->user()->medical_center_id ?? null,
            ]);

            // نمایش نوتیفیکیشن خطا با متغیرهای Livewire
            $this->showNotification = true;
            $this->notificationType = 'error';
            $this->notificationMessage = 'خطا در بارگذاری فایل: ' . $e->getMessage();
            
            // راه‌اندازی تایمر برای مخفی کردن خودکار نوتیفیکیشن
            $this->dispatch('notification-timer');
        }
    }

    /**
     * مخفی کردن نوتیفیکیشن‌ها (برای دکمه بستن نوتیفیکیشن)
     *
     * @return void
     */
    public function hideNotification()
    {
        $this->showNotification = false;
        
        // ثبت لاگ برای دیباگ
        ContractLogger::info('نوتیفیکیشن بسته شد', [
            'contract_id' => $this->contract->id,
            'component' => 'PdfViewer',
            'action' => 'hideNotification',
        ]);
    }

    /**
     * تنظیم تایمر مخفی‌سازی خودکار نوتیفیکیشن‌ها
     * این متد از طرف جاواسکریپت فراخوانی می‌شود
     *
     * @return void
     */
    public function startNotificationTimer()
    {
        // تنظیم یک تایمر برای مخفی کردن نوتیفیکیشن بعد از 5 ثانیه
        // این متد باید توسط جاواسکریپت صدا زده شود
        $this->dispatch('setTimeout', [
            'callback' => '$wire.hideNotification()',
            'time' => 5000
        ]);
    }

    public function render()
    {
        return view('livewire.contracts.pdf-viewer');
    }
}
