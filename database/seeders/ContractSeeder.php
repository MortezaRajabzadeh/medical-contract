<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\MedicalCenter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContractSeeder extends Seeder
{
    /**
     * اجرای سیدر قراردادها
     *
     * @return void
     */
    public function run()
    {
        // دریافت مرکز درمانی اول (ایجاد شده توسط UserRolePermissionSeeder)
        $medicalCenter = MedicalCenter::first();
        
        if (!$medicalCenter) {
            $this->command->error('هیچ مرکز درمانی در سیستم یافت نشد. لطفاً ابتدا UserRolePermissionSeeder را اجرا کنید.');
            return;
        }
        
        // دریافت کاربر ادمین برای قرار دادن به عنوان سازنده قرارداد
        $admin = User::where('email', 'admin@example.com')->first();
        
        if (!$admin) {
            $this->command->error('کاربر ادمین در سیستم یافت نشد. لطفاً ابتدا UserRolePermissionSeeder را اجرا کنید.');
            return;
        }
        
        // ایجاد پوشه برای ذخیره فایل‌ها در مسیر private/contracts
        $contractsDirectory = 'private/contracts';
        if (!Storage::exists($contractsDirectory)) {
            Storage::makeDirectory($contractsDirectory);
        }
        
        // ایجاد یک فایل نمونه PDF برای قرارداد
        // استفاده از همان نام فایلی که در خطا آمده است
        $sampleContent = "<!DOCTYPE html>
<html>
<head>
<title>قرارداد نمونه</title>
</head>
<body style='font-family: sans-serif; direction: rtl; text-align: right;'>
<h1>قرارداد نمونه مرکز درمانی</h1>
<p>این یک قرارداد نمونه برای تست سیستم است.</p>
<p>تاریخ شروع: " . now()->format('Y/m/d') . "</p>
<p>تاریخ پایان: " . now()->addYear()->format('Y/m/d') . "</p>
</body>
</html>";
        
        // استفاده از فایل PDF با نام مشخص شده در خطا
        $fileName = '01K05C9974WAE08020ME6V6C0M.pdf';
        $filePath = $contractsDirectory . '/' . $fileName;
        
        // ایجاد یک فایل HTML موقت
        $tempHtmlPath = storage_path('app/temp_contract.html');
        file_put_contents($tempHtmlPath, $sampleContent);
        
        try {
            // ساخت PDF با استفاده از wkhtmltopdf یا روش جایگزین
            // فعلا برای تست فقط همان فایل HTML را در مسیر PDF قرار می‌دهیم
            Storage::put($filePath, $sampleContent);
            
            $this->command->info("فایل PDF نمونه با موفقیت در مسیر $filePath ایجاد شد");
        } catch (\Exception $e) {
            $this->command->error("خطا در ایجاد فایل PDF: " . $e->getMessage());
        }
        
        // پاک کردن فایل موقت
        if (file_exists($tempHtmlPath)) {
            unlink($tempHtmlPath);
        }
        
        // ایجاد قرارداد نمونه
        $contract = Contract::create([
            'title' => 'قرارداد نمونه',
            'contract_number' => 'C-' . rand(10000, 99999),
            'medical_center_id' => $medicalCenter->id,
            'contract_type' => 'service',
            'vendor_name' => 'شرکت نمونه',
            'vendor_contact' => '09123456789',
            'contract_value' => 5000000.00,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'description' => 'این قرارداد برای تست سیستم ایجاد شده است.',
            'status' => 'active',
            'file_path' => $filePath,
            'original_filename' => $fileName,
            'file_hash' => hash('sha256', $sampleContent),
            'file_size' => strlen($sampleContent),
            'created_by' => $admin->id,
        ]);
        
        $this->command->info('قرارداد نمونه با موفقیت ایجاد شد (شناسه: ' . $contract->id . ')');
    }
}
