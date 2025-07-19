<?php

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CreateContract extends CreateRecord
{
    protected static string $resource = ContractResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    /**
     * پردازش داده‌ها قبل از ایجاد رکورد
     *
     * @param array $data داده‌های فرم
     * @return array داده‌های پردازش شده
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        try {
            // تنظیم شناسه کاربر فعلی برای ستون created_by
            $data['created_by'] = auth()->id();
            
            // فقط اگر فایلی آپلود شده باشد پردازش کنیم
            if (isset($data['file_path']) && !empty($data['file_path'])) {
                // ذخیره نام اصلی فایل
                $data['original_filename'] = pathinfo($data['file_path'], PATHINFO_BASENAME);
                
                // مسیر کامل فایل را بدست آوریم
                $filePath = Storage::path($data['file_path']);
                
                if (file_exists($filePath)) {
                    // محاسبه حجم فایل به کیلوبایت
                    $data['file_size'] = round(filesize($filePath) / 1024);
                    
                    // محاسبه هش فایل
                    $data['file_hash'] = md5_file($filePath);
                } else {
                    // ثبت خطا اگر فایل وجود نداشته باشد
                    Log::error('فایل در مسیر مورد نظر یافت نشد: ' . $filePath);
                }
            }
            
            // ثبت لاگ برای ردیابی و دیباگ
            Log::info('اطلاعات قرارداد برای ثبت', [
                'user_id' => auth()->id(),
                'contract_title' => $data['title'] ?? '',
                'contract_number' => $data['contract_number'] ?? '',
            ]);
        } catch (\Exception $e) {
            // ثبت خطا در لاگ سیستم
            Log::error('خطا در پردازش قرارداد: ' . $e->getMessage());
        }
        
        return $data;
    }
}
