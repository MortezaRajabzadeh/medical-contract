<?php

namespace App\Livewire\Contracts;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Contract;
use Illuminate\Support\Facades\Log;

class ContractUpload extends Component
{
    use WithFileUploads;

    // حالت کامپوننت: 'new' برای قرارداد جدید، 'signed' برای آپلود قرارداد امضا شده
    public $mode = 'new';
    
    // شناسه قرارداد (برای حالت آپلود امضا شده)
    public $contractId;
    
    // شناسه مرکز درمانی
    public $medicalCenterId;
    
    // وضعیت فعلی قرارداد
    public $currentStatus;
    
    // فایل امضا شده فعلی (اگر وجود داشته باشد)
    public $currentSignedFile;
    
    #[Validate('required_if:mode,new|mimes:pdf|max:10240')]
    public $contractFile;
    
    #[Validate('required_if:mode,signed|mimes:pdf|max:10240')]
    public $signedFile;
    
    #[Validate('required_if:mode,new|string|max:255')]
    public $title;
    
    #[Validate('nullable|string')]
    public $description;
    
    #[Validate('required_if:mode,new|in:service,equipment,pharmaceutical,maintenance,consulting')]
    public $contractType;
    
    #[Validate('required_if:mode,new|string|max:255')]
    public $vendorName;
    
    #[Validate('required_if:mode,new|decimal:0,2|min:0')]
    public $contractValue;
    
    #[Validate('required_if:mode,new|date')]
    public $startDate;
    
    #[Validate('required_if:mode,new|date|after:start_date')]
    public $endDate;
    
    // تنظیم اولیه کامپوننت براساس پارامترهای ورودی
    public function mount($contractId = null, $medicalCenterId = null, $mode = 'new')
    {
        $this->mode = $mode;
        $this->contractId = $contractId;
        $this->medicalCenterId = $medicalCenterId;
        
        // اگر در حالت آپلود قرارداد امضا شده هستیم
        if ($this->mode === 'signed' && $this->contractId) {
            $this->loadContractData();
        }
    }
    
    // بارگذاری اطلاعات قرارداد موجود
    protected function loadContractData()
    {
        try {
            $contract = Contract::findOrFail($this->contractId);
            
            // بررسی دسترسی مرکز درمانی به این قرارداد
            if (Auth::user()->medical_center_id !== $contract->medical_center_id) {
                $this->addError('access', 'شما مجوز دسترسی به این قرارداد را ندارید.');
                return;
            }
            
            $this->currentStatus = $contract->status;
            
            // بررسی وجود فایل امضا شده قبلی
            if ($contract->signed_file_path) {
                $this->currentSignedFile = [
                    'filename' => $contract->signed_original_filename ?: 'قرارداد امضا شده.pdf',
                    'path' => $contract->signed_file_path
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('خطا در بارگذاری اطلاعات قرارداد: ' . $e->getMessage(), [
                'contract_id' => $this->contractId,
                'user_id' => Auth::id()
            ]);
            $this->addError('contract', 'خطا در بارگذاری اطلاعات قرارداد. لطفا مجددا تلاش کنید.');
        }
    }

    // ذخیره قرارداد جدید
    public function saveNewContract()
    {
        // اعتبارسنجی فقط برای فیلدهای مورد نیاز در حالت قرارداد جدید
        $this->validate([
            'contractFile' => 'required|mimes:pdf|max:10240',
            'title' => 'required|string|max:255',
            'contractType' => 'required|in:service,equipment,pharmaceutical,maintenance,consulting',
            'vendorName' => 'required|string|max:255',
            'contractValue' => 'required|decimal:0,2|min:0',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
        ]);

        try {
            // تولید نام فایل امن
            $filename = Str::uuid() . '.' . $this->contractFile->getClientOriginalExtension();
            
            // محاسبه هش فایل برای اطمینان از صحت
            $fileHash = hash_file('sha256', $this->contractFile->getRealPath());
            
            // ذخیره امن فایل
            $path = $this->contractFile->storeAs('contracts', $filename, 'private');

            // ایجاد رکورد قرارداد
            Contract::create([
                'contract_number' => $this->generateContractNumber(),
                'medical_center_id' => Auth::user()->medical_center_id,
                'title' => $this->title,
                'description' => $this->description,
                'contract_type' => $this->contractType,
                'vendor_name' => $this->vendorName,
                'contract_value' => $this->contractValue,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'file_path' => $path,
                'original_filename' => $this->contractFile->getClientOriginalName(),
                'file_hash' => $fileHash,
                'file_size' => $this->contractFile->getSize(),
                'created_by' => Auth::id(),
                'status' => 'pending',
            ]);

            // ثبت فعالیت - commented out until spatie/laravel-activitylog is installed
            // activity()
            //     ->causedBy(auth()->user())
            //     ->log('قرارداد جدید آپلود شد: ' . $this->title);

            session()->flash('message', 'قرارداد با موفقیت آپلود شد!');
            $this->reset(['contractFile', 'title', 'description', 'contractType', 'vendorName', 'contractValue', 'startDate', 'endDate']);
        } catch (\Exception $e) {
            Log::error('خطا در ذخیره قرارداد جدید: ' . $e->getMessage(), [
                'user_id' => Auth::id()
            ]);
            session()->flash('error', 'خطا در آپلود قرارداد. لطفا مجددا تلاش کنید.');
        }
    }

    // آپلود قرارداد امضا شده
    public function uploadSignedContract()
    {
        // اعتبارسنجی فقط برای فایل امضا شده
        $this->validate([
            'signedFile' => 'required|mimes:pdf|max:10240',
        ]);

        try {
            $contract = Contract::findOrFail($this->contractId);
            
            // بررسی دسترسی مرکز درمانی به این قرارداد
            if (Auth::user()->medical_center_id !== $contract->medical_center_id) {
                $this->addError('access', 'شما مجوز دسترسی به این قرارداد را ندارید.');
                return;
            }
            
            // تولید نام فایل امن
            $filename = 'signed_' . Str::uuid() . '.pdf';
            
            // محاسبه هش فایل برای اطمینان از صحت
            $fileHash = hash_file('sha256', $this->signedFile->getRealPath());
            
            // ذخیره امن فایل
            $path = $this->signedFile->storeAs('contracts/signed', $filename, 'private');
            
            // حذف فایل امضا شده قبلی اگر وجود داشته باشد
            if ($contract->signed_file_path && Storage::disk('private')->exists($contract->signed_file_path)) {
                Storage::disk('private')->delete($contract->signed_file_path);
            }
            
            // به‌روزرسانی قرارداد
            $contract->update([
                'signed_file_path' => $path,
                'signed_original_filename' => $this->signedFile->getClientOriginalName(),
                'signed_file_hash' => $fileHash,
                'signed_file_size' => $this->signedFile->getSize(),
                'signed_date' => now(), // اضافه کردن تاریخ امضا
                'status' => 'signed', // تغییر وضعیت به "امضا شده"
            ]);
            
            // ثبت فعالیت - commented out until spatie/laravel-activitylog is installed
            // activity()
            //     ->performedOn($contract)
            //     ->causedBy(auth()->user())
            //     ->log('قرارداد امضا شده آپلود شد');
                
            // بارگذاری مجدد اطلاعات قرارداد
            $this->loadContractData();
            $this->reset(['signedFile']);
            
            session()->flash('message', 'قرارداد امضا شده با موفقیت آپلود شد.');
        } catch (\Exception $e) {
            Log::error('خطا در آپلود قرارداد امضا شده: ' . $e->getMessage(), [
                'contract_id' => $this->contractId,
                'user_id' => Auth::id()
            ]);
            session()->flash('error', 'خطا در آپلود قرارداد امضا شده. لطفا مجددا تلاش کنید.');
        }
    }
    
    // حذف قرارداد امضا شده
    public function deleteSignedContract()
    {
        try {
            $contract = Contract::findOrFail($this->contractId);
            
            // بررسی دسترسی مرکز درمانی به این قرارداد
            if (Auth::user()->medical_center_id !== $contract->medical_center_id) {
                $this->addError('access', 'شما مجوز دسترسی به این قرارداد را ندارید.');
                return;
            }
            
            // اگر وضعیت قرارداد "تایید شده" است، اجازه حذف نمی‌دهیم
            if ($contract->status === 'approved') {
                $this->addError('status', 'قرارداد تایید شده قابل حذف نیست.');
                return;
            }
            
            // حذف فایل امضا شده
            if ($contract->signed_file_path && Storage::disk('private')->exists($contract->signed_file_path)) {
                Storage::disk('private')->delete($contract->signed_file_path);
            }
            
            // به‌روزرسانی قرارداد
            $contract->update([
                'signed_file_path' => null,
                'signed_original_filename' => null,
                'signed_file_hash' => null,
                'signed_file_size' => null,
                'signed_at' => null,
                'status' => 'pending', // تغییر وضعیت به "در انتظار امضاء"
            ]);
            
            // ثبت فعالیت - commented out until spatie/laravel-activitylog is installed
            // activity()
            //     ->performedOn($contract)
            //     ->causedBy(auth()->user())
            //     ->log('قرارداد امضا شده حذف شد');
                
            // بارگذاری مجدد اطلاعات قرارداد
            $this->loadContractData();
            
            session()->flash('message', 'قرارداد امضا شده با موفقیت حذف شد.');
        } catch (\Exception $e) {
            Log::error('خطا در حذف قرارداد امضا شده: ' . $e->getMessage(), [
                'contract_id' => $this->contractId,
                'user_id' => Auth::id()
            ]);
            session()->flash('error', 'خطا در حذف قرارداد امضا شده. لطفا مجددا تلاش کنید.');
        }
    }
    
    // ذخیره براساس حالت کامپوننت
    public function save()
    {
        if ($this->mode === 'new') {
            $this->saveNewContract();
        } else if ($this->mode === 'signed') {
            $this->uploadSignedContract();
        }
    }

    private function generateContractNumber()
    {
        $center = Auth::user()->medicalCenter;
        $year = date('Y');
        $sequence = Contract::where('medical_center_id', $center->id)
            ->whereYear('created_at', $year)
            ->count() + 1;
            
        return sprintf('%s-%s-%04d', $center->code, $year, $sequence);
    }

    public function render()
    {
        if ($this->mode === 'signed') {
            return view('livewire.contracts.upload-signed');
        }
        
        return view('livewire.contracts.upload');
    }
}
