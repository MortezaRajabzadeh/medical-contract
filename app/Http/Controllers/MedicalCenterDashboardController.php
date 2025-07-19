<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\MedicalCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MedicalCenterDashboardController extends Controller
{
    use AuthorizesRequests;
    /**
     * نمایش لیست قراردادهای مرکز درمانی
     *
     * @return \Illuminate\View\View
     */
    public function contracts()
    {
        try {
            $user = Auth::user();
            $medicalCenter = MedicalCenter::where('id', $user->medical_center_id)->first();

            if (!$medicalCenter) {
                Log::error('کاربر با شناسه '.$user->id.' به مرکز درمانی متصل نیست.');
                return redirect()->route('medical-center.dashboard')
                    ->with('error', 'شما به هیچ مرکز درمانی متصل نیستید.');
            }

            $contracts = Contract::where('medical_center_id', $medicalCenter->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10); // استفاده از پیجینیشن به جای get()

            return view('medical-center.contracts.index', compact('contracts', 'medicalCenter'));
        } catch (\Exception $e) {
            Log::error('خطا در نمایش لیست قراردادها: '.$e->getMessage());
            return redirect()->route('medical-center.dashboard')
                ->with('error', 'خطایی در بارگذاری لیست قراردادها رخ داد. لطفاً مجدداً تلاش کنید.');
        }
    }

    /**
     * نمایش جزئیات قرارداد
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function viewContract($id)
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
            $contract = Contract::findOrFail($id);
            
            // بررسی مجوز با استفاده از پالیسی
            $this->authorize('view', $contract);
            
            // اگر به این نقطه برسیم، یعنی کاربر مجوز دیدن قرارداد را دارد
            
            return view('medical-center.contracts.view', compact('contract', 'medicalCenter'));
        } catch (\Exception $e) {
            Log::error('خطا در نمایش جزئیات قرارداد: '.$e->getMessage());
            return redirect()->route('medical-center.contracts.index')
                ->with('error', 'خطایی در بارگذاری جزئیات قرارداد رخ داد. لطفاً مجدداً تلاش کنید.');
        }
    }

    /**
     * دانلود فایل قرارداد
     *
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadContract($id)
    {
        try {
            $user = Auth::user();
            $medicalCenter = MedicalCenter::where('id', $user->medical_center_id)->first();

            if (!$medicalCenter) {
                Log::error('کاربر با شناسه '.$user->id.' به مرکز درمانی متصل نیست.');
                return redirect()->route('medical-center.dashboard')
                    ->with('error', 'شما به هیچ مرکز درمانی متصل نیستید.');
            }

            $contract = Contract::where('id', $id)
                ->where('medical_center_id', $medicalCenter->id)
                ->first();

            if (!$contract) {
                Log::error('قرارداد با شناسه '.$id.' برای مرکز درمانی با شناسه '.$medicalCenter->id.' یافت نشد.');
                return redirect()->route('medical-center.contracts.index')
                    ->with('error', 'قرارداد مورد نظر یافت نشد.');
            }

            // جداسازی بخش 'private/contracts/' از ابتدای مسیر برای استفاده با دیسک private
            $filePath = $contract->file_path;
            if (strpos($filePath, 'private/') === 0) {
                $filePath = substr($filePath, strlen('private/'));
            }

            if (!$contract->file_path || !Storage::disk('private')->exists($filePath)) {
                return redirect()->route('medical-center.contracts.view', $contract->id)
                    ->with('error', 'فایل قرارداد موجود نیست.');
            }

            // استفاده از دیسک private برای دانلود
            return Storage::disk('private')->download($filePath, $contract->title . '.pdf');
        } catch (\Exception $e) {
            Log::error('خطا در دانلود فایل قرارداد: '.$e->getMessage());
            return redirect()->route('medical-center.contracts.index')
                ->with('error', 'خطایی در دانلود فایل قرارداد رخ داد. لطفاً مجدداً تلاش کنید.');
        }
    }

    /**
     * دانلود فایل قرارداد امضا شده
     *
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadSignedContract($id)
    {
        try {
            $user = Auth::user();
            $medicalCenter = MedicalCenter::where('id', $user->medical_center_id)->first();

            if (!$medicalCenter) {
                Log::error('کاربر با شناسه '.$user->id.' به مرکز درمانی متصل نیست.');
                return redirect()->route('medical-center.dashboard')
                    ->with('error', 'شما به هیچ مرکز درمانی متصل نیستید.');
            }

            $contract = Contract::where('id', $id)
                ->where('medical_center_id', $medicalCenter->id)
                ->first();

            if (!$contract) {
                Log::error('قرارداد با شناسه '.$id.' برای مرکز درمانی با شناسه '.$medicalCenter->id.' یافت نشد.');
                return redirect()->route('medical-center.contracts.index')
                    ->with('error', 'قرارداد مورد نظر یافت نشد.');
            }

            // جداسازی بخش 'private/' از ابتدای مسیر برای استفاده با دیسک private
            $signedFilePath = $contract->signed_file_path;
            if (strpos($signedFilePath, 'private/') === 0) {
                $signedFilePath = substr($signedFilePath, strlen('private/'));
            }
            
            if (!$contract->signed_file_path || !Storage::disk('private')->exists($signedFilePath)) {
                Log::error('فایل امضا شده قرارداد با مسیر '.$contract->signed_file_path.' یافت نشد. مسیر کامل: ' . storage_path('app/private/' . $signedFilePath));
                return redirect()->route('medical-center.contracts.view', $contract->id)
                    ->with('error', 'فایل امضا شده قرارداد موجود نیست.');
            }
            
            // استفاده از دیسک private برای دانلود
            return Storage::disk('private')->download($signedFilePath, $contract->title . '-signed.pdf');
        } catch (\Exception $e) {
            Log::error('خطا در دانلود فایل امضا شده قرارداد: '.$e->getMessage());
            return redirect()->route('medical-center.contracts.index')
                ->with('error', 'خطایی در دانلود فایل امضا شده قرارداد رخ داد. لطفاً مجدداً تلاش کنید.');
        }
    }
}
