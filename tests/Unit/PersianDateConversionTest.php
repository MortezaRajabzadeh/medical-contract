<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\ContractController;
use App\Models\Contract;
use App\Models\MedicalCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;
use ReflectionClass;
use Tests\TestCase;

class PersianDateConversionTest extends TestCase
{
    use RefreshDatabase;

    private $controller;
    private $convertPersianMethod;

    protected function setUp(): void
    {
        parent::setUp();
        
        // ایجاد نمونه از کنترلر
        $this->controller = new ContractController();
        
        // دسترسی به متد محافظت شده با استفاده از Reflection
        $reflector = new ReflectionClass(ContractController::class);
        $this->convertPersianMethod = $reflector->getMethod('convertPersianNumbersToEnglish');
        $this->convertPersianMethod->setAccessible(true);
    }

    /**
     * تست تبدیل اعداد فارسی به انگلیسی
     *
     * @return void
     */
    public function test_convert_persian_numbers_to_english(): void
    {
        // تست با اعداد فارسی
        $persianDate = '۱۴۰۴/۰۴/۲۵';
        $englishDate = $this->convertPersianMethod->invoke($this->controller, $persianDate);
        
        $this->assertEquals('1404/04/25', $englishDate);
        
        // تست با اعداد مخلوط فارسی و انگلیسی
        $mixedDate = '۱۴04/۰4/25';
        $englishMixedDate = $this->convertPersianMethod->invoke($this->controller, $mixedDate);
        
        $this->assertEquals('1404/04/25', $englishMixedDate);
        
        // لاگ برای ردیابی
        \Log::info('تست تبدیل اعداد فارسی به انگلیسی با موفقیت انجام شد.');
    }

    /**
     * تست تاریخ فارسی در فرم افزودن قرارداد
     *
     * @return void
     */
    public function test_contract_creation_with_persian_dates(): void
    {
        // ایجاد یک مرکز درمانی برای تست
        $medicalCenter = MedicalCenter::factory()->create();
        
        // ایجاد یک کاربر برای تست
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // داده‌های فرم با تاریخ فارسی
        $formData = [
            'medical_center_id' => $medicalCenter->id,
            'contract_number' => 'TEST-1001',
            'title' => 'قرارداد تست',
            'start_date' => '۱۴۰۴/۰۱/۰۱',
            'end_date' => '۱۴۰۵/۰۱/۰۱',
            'status' => 'active',
            'description' => 'این یک قرارداد تست است'
        ];
        
        // ایجاد درخواست با داده‌های فرم
        $request = Request::create('/admin/contracts', 'POST', $formData);
        
        try {
            // فراخوانی متد store در کنترلر با استفاده از reflection
            $reflector = new ReflectionClass(ContractController::class);
            $storeMethod = $reflector->getMethod('store');
            $storeMethod->setAccessible(true);
            
            // اجرای متد
            $response = $storeMethod->invoke($this->controller, $request);
            
            // بررسی اینکه آیا قرارداد در دیتابیس ذخیره شده است
            $this->assertDatabaseHas('contracts', [
                'contract_number' => 'TEST-1001',
                'title' => 'قرارداد تست'
            ]);
            
            // بررسی مقادیر پیش فرض فیلدهای حذف شده
            $contract = Contract::where('contract_number', 'TEST-1001')->first();
            $this->assertNull($contract->signed_date, 'تاریخ امضا باید null باشد');
            $this->assertEquals(0, $contract->amount, 'مبلغ قرارداد باید 0 باشد');
            
            \Log::info('تست ایجاد قرارداد با تاریخ فارسی با موفقیت انجام شد.', [
                'contract_id' => $contract->id ?? null,
                'start_date' => $contract->start_date ?? null,
                'end_date' => $contract->end_date ?? null
            ]);
            
        } catch (\Exception $e) {
            $this->fail('خطا در اجرای تست: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }
    
    /**
     * تست ولیدیشن فرم بدون فیلدهای حذف شده
     *
     * @return void
     */
    public function test_contract_validation_without_removed_fields(): void
    {
        // داده‌های فرم بدون فیلدهای مبلغ قرارداد و تاریخ امضا
        $medicalCenter = MedicalCenter::factory()->create();
        
        $formData = [
            'medical_center_id' => $medicalCenter->id,
            'contract_number' => 'TEST-1002',
            'title' => 'قرارداد تست ولیدیشن',
            'start_date' => '1404/01/01',
            'end_date' => '1405/01/01',
            'status' => 'active',
            'description' => 'این یک قرارداد تست برای ولیدیشن است'
        ];
        
        // اعمال قوانین اعتبارسنجی
        $rules = [
            'medical_center_id' => 'required|exists:medical_centers,id',
            'contract_number' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required',
            'description' => 'nullable|string|max:1000',
        ];
        
        $validator = Validator::make($formData, $rules);
        
        // بررسی اعتبارسنجی
        $this->assertFalse($validator->fails(), 'ولیدیشن باید موفق باشد بدون فیلدهای حذف شده');
        
        \Log::info('تست ولیدیشن فرم بدون فیلدهای حذف شده با موفقیت انجام شد.');
    }
}
