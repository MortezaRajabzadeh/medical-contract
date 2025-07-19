<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\MedicalCenter;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MedicalCenterContractController extends Controller
{
    use AuthorizesRequests;

    /**
     * نمایش لیست قراردادهای مرکز درمانی
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $medicalCenter = MedicalCenter::where('id', $user->medical_center_id)->first();

            if (!$medicalCenter) {
                Log::error('کاربر با شناسه '.$user->id.' به مرکز درمانی متصل نیست.');
                return redirect()->route('medical-center.dashboard')
                    ->with('error', 'شما به هیچ مرکز درمانی متصل نیستید.');
            }

            $contracts = Contract::with('medicalCenter') // Eager Loading برای بهبود پرفورمنس
                ->where('medical_center_id', $medicalCenter->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view('medical-center.contracts.index', compact('contracts', 'medicalCenter'));
        } catch (\Exception $e) {
            Log::error('خطا در نمایش لیست قراردادها: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'medical_center_id' => Auth::user()->medical_center_id ?? null
            ]);
            
            return redirect()->route('medical-center.dashboard')
                ->with('error', 'خطایی در بارگذاری لیست قراردادها رخ داد. لطفاً مجدداً تلاش کنید.');
        }
    }

    /**
     * نمایش جزئیات قرارداد
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function show(string $id)
    {
        try {
            $user = Auth::user();
            $medicalCenter = MedicalCenter::where('id', $user->medical_center_id)->first();

            if (!$medicalCenter) {
                Log::error('کاربر با شناسه '.$user->id.' به مرکز درمانی متصل نیست.');
                return redirect()->route('medical-center.dashboard')
                    ->with('error', 'شما به هیچ مرکز درمانی متصل نیستید.');
            }
            
            // ابتدا قرارداد را پیدا کنیم (بدون محدودیت medical_center_id)
            $contract = Contract::with('medicalCenter') // Eager Loading
                ->findOrFail($id);
            
            // بررسی مجوز با استفاده از پالیسی
            $this->authorize('view', $contract);
            
            return view('medical-center.contracts.view', compact('contract', 'medicalCenter'));
            
        } catch (AuthorizationException $e) {
            Log::warning('تلاش غیرمجاز برای مشاهده قرارداد: ', [
                'user_id' => Auth::id(),
                'contract_id' => $id,
                'medical_center_id' => Auth::user()->medical_center_id ?? null
            ]);
            
            return redirect()->route('medical-center.contracts.index')
                ->with('error', 'شما مجوز دسترسی به این قرارداد را ندارید.');
                
        } catch (\Exception $e) {
            Log::error('خطا در نمایش جزئیات قرارداد: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'contract_id' => $id
            ]);
            
            return redirect()->route('medical-center.contracts.index')
                ->with('error', 'خطایی در بارگذاری جزئیات قرارداد رخ داد. لطفاً مجدداً تلاش کنید.');
        }
    }

    /**
     * دانلود فایل قرارداد
     *
     * @param string $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    /**
     * نمایش مستقیم فایل PDF برای iframe
     *
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function viewFile(string $id)
    {
        try {
            // یافتن قرارداد
            $contract = Contract::findOrFail($id);
            
            // بررسی مجوز با استفاده از پالیسی
            $this->authorize('view', $contract);
            
            // بررسی وجود مسیر فایل در دیتابیس
            if (empty($contract->file_path)) {
                Log::error('مسیر فایل قرارداد در دیتابیس وجود ندارد', [
                    'contract_id' => $contract->id,
                    'user_id' => Auth::id(),
                    'original_filename' => $contract->original_filename ?? null
                ]);
                
                return response()->json(['error' => 'فایل قرارداد در سیستم ثبت نشده است'], 404);
            }
            
            // استخراج نام فایل از مسیر فایل
            $fileName = basename($contract->file_path);
            
            // ساخت مسیرهای ممکن برای فایل
            $filePath = 'public/private/contracts/' . $fileName;
            $alternativePath = 'private/contracts/' . $fileName;
            $directPath = $contract->file_path; // مسیر مستقیم از دیتابیس
            
            // بررسی وجود فایل در مسیرهای مختلف
            $fileExists = Storage::exists($filePath);
            $usedPath = $filePath;
            
            // اگر فایل در مسیر اصلی نبود، مسیرهای دیگر را بررسی کن
            if (!$fileExists) {
                Log::warning('فایل در مسیر اصلی یافت نشد. بررسی مسیرهای دیگر...', [
                    'primary_path' => $filePath,
                    'alternative_path' => $alternativePath,
                    'direct_path' => $directPath,
                    'contract_id' => $contract->id
                ]);
                
                // بررسی مسیر جایگزین
                if (Storage::exists($alternativePath)) {
                    $filePath = $alternativePath;
                    $fileExists = true;
                    $usedPath = $alternativePath;
                }
                // بررسی مسیر مستقیم از دیتابیس
                else if (Storage::exists($directPath)) {
                    $filePath = $directPath;
                    $fileExists = true;
                    $usedPath = $directPath;
                }
            }
            
            // اگر فایل در هیچ مسیری یافت نشد، خطا نمایش بده
            if (!$fileExists) {
                Log::error('فایل قرارداد در هیچ مسیری یافت نشد', [
                    'contract_id' => $contract->id,
                    'user_id' => Auth::id(),
                    'paths_checked' => [$filePath, $alternativePath, $directPath]
                ]);
                
                return response()->json(['error' => 'فایل قرارداد یافت نشد'], 404);
            }

            // ثبت لاگ برای دیباگ نمایش فایل
            Log::info('نمایش فایل PDF در iframe', [
                'contract_id' => $contract->id,
                'path_used' => $usedPath,
                'user_id' => Auth::id()
            ]);
            
            // برگرداندن فایل با هدرهای مناسب برای نمایش در iframe
            return response()->file(Storage::path($filePath), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . ($contract->original_filename ?? 'contract.pdf') . '"'
            ]);
            
        } catch (AuthorizationException $e) {
            Log::warning('تلاش غیرمجاز برای مشاهده فایل قرارداد: ', [
                'user_id' => Auth::id(),
                'contract_id' => $id,
                'message' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'شما مجوز دسترسی به این فایل را ندارید'], 403);
                
        } catch (ModelNotFoundException $e) {
            Log::error('قرارداد مورد نظر یافت نشد: ' . $id, [
                'user_id' => Auth::id()
            ]);
            
            return response()->json(['error' => 'قرارداد مورد نظر یافت نشد'], 404);
                
        } catch (\Exception $e) {
            Log::error('خطا در نمایش فایل قرارداد: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'contract_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'خطایی در بارگذاری فایل رخ داد'], 500);
        }
    }

    public function download(string $id)
    {
        try {
            // یافتن قرارداد
            $contract = Contract::findOrFail($id);
            
            // بررسی مجوز با استفاده از پالیسی
            $this->authorize('download', $contract);
            
            // بررسی وجود مسیر فایل در دیتابیس
            if (empty($contract->file_path)) {
                Log::error('مسیر فایل قرارداد در دیتابیس وجود ندارد', [
                    'contract_id' => $contract->id,
                    'user_id' => Auth::id(),
                    'original_filename' => $contract->original_filename,
                    'file_path' => $contract->file_path
                ]);
                
                return redirect()->back()
                    ->with('error', 'فایل قرارداد در سیستم ثبت نشده است. لطفاً با پشتیبانی تماس بگیرید.');
            }
            // استخراج نام فایل از مسیر فایل
            $fileName = basename($contract->file_path);
            
            // ساخت مسیرهای ممکن برای فایل
            $filePath = 'public/private/contracts/' . $fileName;
            $alternativePath = 'private/contracts/' . $fileName;
            $directPath = $contract->file_path; // مسیر مستقیم از دیتابیس
            
            // بررسی وجود پوشه
            if (!Storage::exists('public/private/contracts')) {
                // ایجاد پوشه اگر وجود ندارد
                Storage::makeDirectory('public/private/contracts');
                
                Log::warning('پوشه قراردادها وجود نداشت و ایجاد شد', [
                    'path' => 'public/private/contracts',
                    'contract_id' => $contract->id,
                    'user_id' => Auth::id()
                ]);
            }
            
            // بررسی وجود فایل در مسیرهای مختلف
            $fileExists = Storage::exists($filePath);
            $useAlternativePath = false;
            $useDirectPath = false;
            $usedPath = $filePath;
            
            // اگر فایل در مسیر اصلی نبود، مسیرهای دیگر را بررسی کن
            if (!$fileExists) {
                Log::warning('فایل در مسیر اصلی یافت نشد. بررسی مسیرهای دیگر...', [
                    'primary_path' => $filePath,
                    'alternative_path' => $alternativePath,
                    'direct_path' => $directPath,
                    'contract_id' => $contract->id,
                    'file_path_in_db' => $contract->file_path
                ]);
                
                // بررسی مسیر جایگزین
                if (Storage::exists($alternativePath)) {
                    $filePath = $alternativePath;
                    $fileExists = true;
                    $useAlternativePath = true;
                    $usedPath = $alternativePath;
                    
                    Log::info('فایل در مسیر جایگزین یافت شد', [
                        'path' => $alternativePath,
                        'contract_id' => $contract->id
                    ]);
                }
                // بررسی مسیر مستقیم از دیتابیس
                else if (Storage::exists($directPath)) {
                    $filePath = $directPath;
                    $fileExists = true;
                    $useDirectPath = true;
                    $usedPath = $directPath;
                    
                    Log::info('فایل در مسیر مستقیم یافت شد', [
                        'path' => $directPath,
                        'contract_id' => $contract->id
                    ]);
                }
            } else {
                $usedPath = $filePath;
                Log::info('فایل در مسیر اصلی یافت شد', [
                    'path' => $filePath,
                    'contract_id' => $contract->id
                ]);
            }
            
            // اگر فایل در هیچ مسیری یافت نشد، خطا نمایش بده
            if (!$fileExists) {
                Log::error('فایل قرارداد در هیچ مسیری یافت نشد', [
                    'contract_id' => $contract->id,
                    'user_id' => Auth::id(),
                    'file_name' => $contract->file,
                    'paths_checked' => [$filePath, $alternativePath]
                ]);
                
                return redirect()->back()
                    ->with('error', 'فایل قرارداد یافت نشد. لطفاً با پشتیبانی تماس بگیرید.');
            }
            
            // بررسی و محاسبه اندازه فایل
            try {
                // اگر اندازه فایل در دیتابیس ذخیره نشده باشد
                if (empty($contract->file_size)) {
                    $fileSize = Storage::size($filePath);
                    
                    if ($fileSize <= 0) {
                        Log::warning('فایل خالی یا نامعتبر است', [
                            'contract_id' => $contract->id,
                            'file_path' => $filePath,
                            'file_size' => $fileSize,
                            'using_alternative_path' => $useAlternativePath
                        ]);
                        
                        return redirect()->back()
                            ->with('error', 'فایل قرارداد خالی یا نامعتبر است. لطفاً با پشتیبانی تماس بگیرید.');
                    }
                    
                    // به‌روزرسانی اندازه فایل در دیتابیس
                    $contract->file_size = $fileSize;
                    $contract->save();
                    
                    Log::info('اندازه فایل محاسبه و در دیتابیس ذخیره شد', [
                        'contract_id' => $contract->id,
                        'file_size' => $fileSize,
                        'file_path' => $filePath,
                        'using_alternative_path' => $useAlternativePath
                    ]);
                }
                
                // تعیین نام فایل برای دانلود
                $downloadFilename = $contract->original_filename;
                if (empty($downloadFilename)) {
                    $downloadFilename = basename($filePath) ?: 'قرارداد-' . $contract->id . '.pdf';
                    
                    // آپدیت نام فایل اصلی در دیتابیس
                    $contract->original_filename = $downloadFilename;
                    $contract->save();
                    Log::info('نام فایل اصلی در دیتابیس آپدیت شد', [
                        'contract_id' => $contract->id,
                        'original_filename' => $downloadFilename
                    ]);
                }
                
                $fileSize = $contract->file_size > 0 ? $contract->file_size : Storage::size($filePath);
                
                Log::info('دانلود فایل در حال انجام', [
                    'contract_id' => $contract->id,
                    'filename' => $downloadFilename,
                    'path' => $filePath,
                    'size' => $fileSize
                ]);
                
                // دانلود فایل
                return Storage::download($filePath, $downloadFilename, [
                    'Content-Type' => 'application/octet-stream',
                    'Content-Length' => $fileSize,
                    'Content-Disposition' => 'attachment; filename="' . $downloadFilename . '"'
                ]);
            } catch (\Exception $e) {
                Log::error('خطای محاسبه اندازه فایل در مسیر: ' . $filePath, [
                    'error' => $e->getMessage(),
                    'contract_id' => $contract->id,
                    'file_path' => $filePath,
                    'using_alternative_path' => $useAlternativePath,
                    'stack_trace' => $e->getTraceAsString()
                ]);
                
                // حتی اگر خطای محاسبه اندازه فایل رخ دهد، سعی کنیم فایل را دانلود کنیم
                
                // سعی می‌کنیم بدون محاسبه اندازه فایل، آن را دانلود کنیم
                try {
                    // تعیین نام فایل برای دانلود
                    $downloadFilename = $contract->original_filename;
                    if (empty($downloadFilename)) {
                        $downloadFilename = basename($filePath) ?: 'قرارداد-' . $contract->id . '.pdf';
                    }
                    
                    Log::info('تلاش مجدد برای دانلود فایل با نام اصلی', [
                        'contract_id' => $contract->id,
                        'filename' => $downloadFilename
                    ]);
                    
                    return Storage::download($filePath, $downloadFilename);
                } catch (\Exception $downloadEx) {
                    Log::error('خطای دانلود فایل', [
                        'error' => $downloadEx->getMessage(),
                        'contract_id' => $contract->id, 
                        'file_path' => $filePath,
                        'stack_trace' => $downloadEx->getTraceAsString()
                    ]);
                    
                    return redirect()->back()
                        ->with('error', 'خطا در دانلود فایل. لطفاً با پشتیبانی تماس بگیرید.');
                }
            }
            
        } catch (AuthorizationException $e) {
            Log::warning('تلاش غیرمجاز برای دانلود قرارداد: ', [
                'user_id' => Auth::id(),
                'contract_id' => $id,
                'message' => $e->getMessage()
            ]);
            
            return redirect()->route('medical-center.contracts.index')
                ->with('error', 'شما مجوز دانلود این قرارداد را ندارید.');
                
        } catch (ModelNotFoundException $e) {
            Log::error('قرارداد مورد نظر یافت نشد: ' . $id, [
                'user_id' => Auth::id()
            ]);
            
            return redirect()->route('medical-center.contracts.index')
                ->with('error', 'قرارداد مورد نظر یافت نشد.');
                
        } catch (\Exception $e) {
            Log::error('خطا در دانلود قرارداد: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'contract_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('medical-center.contracts.index')
                ->with('error', 'خطایی در دانلود قرارداد رخ داد. لطفاً مجدداً تلاش کنید.');
        }
    }
}
