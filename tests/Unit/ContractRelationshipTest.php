<?php

namespace Tests\Unit;

use App\Models\Contract;
use App\Models\MedicalCenter;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * تست رابطه بین قرارداد و مرکز درمانی
     */
    public function test_contract_belongs_to_medical_center(): void
    {
        // ایجاد نمونه از مدل قرارداد
        $contract = new Contract();
        
        // بررسی نوع رابطه
        $relation = $contract->medicalCenter();
        
        // تأیید اینکه رابطه از نوع BelongsTo است
        $this->assertInstanceOf(BelongsTo::class, $relation);
        
        // لاگ برای ردیابی
        \Log::info('تست رابطه Contract->medicalCenter با موفقیت انجام شد.');
    }

    /**
     * تست رابطه بین مرکز درمانی و قراردادها
     */
    public function test_medical_center_has_many_contracts(): void
    {
        // ایجاد نمونه از مدل مرکز درمانی
        $medicalCenter = new MedicalCenter();
        
        // بررسی نوع رابطه
        $relation = $medicalCenter->contracts();
        
        // تأیید اینکه رابطه از نوع HasMany است
        $this->assertInstanceOf(HasMany::class, $relation);
        
        // لاگ برای ردیابی
        \Log::info('تست رابطه MedicalCenter->contracts با موفقیت انجام شد.');
    }

    /**
     * تست عملی ایجاد قرارداد با مرکز درمانی
     */
    public function test_create_contract_with_medical_center_relationship(): void
    {
        // ایجاد یک کاربر برای فیلد created_by
        $user = User::factory()->create();
        
        // ایجاد یک مرکز درمانی
        $medicalCenter = MedicalCenter::create([
            'name' => 'مرکز تست',
            'license_number' => 'TEST-123',
            'phone' => '09123456789',
            'email' => 'test@example.com'
        ]);
        
        // ایجاد یک قرارداد مرتبط با مرکز درمانی
        $contract = Contract::create([
            'contract_number' => 'CNT-' . rand(1000, 9999),
            'medical_center_id' => $medicalCenter->id,
            'title' => 'قرارداد تست',
            'contract_type' => 'service',
            'vendor_name' => 'فروشنده تست',
            'contract_value' => 1000000,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'status' => 'active',
            'created_by' => $user->id
        ]);
        
        // بازیابی قرارداد از دیتابیس
        $fetchedContract = Contract::find($contract->id);
        
        // بررسی رابطه قرارداد با مرکز درمانی
        $this->assertEquals($medicalCenter->id, $fetchedContract->medicalCenter->id);
        $this->assertEquals($medicalCenter->name, $fetchedContract->medicalCenter->name);
        
        // بررسی رابطه مرکز درمانی با قراردادها
        $this->assertTrue($medicalCenter->contracts->contains($contract));
        
        // لاگ برای ردیابی
        \Log::info('تست ایجاد قرارداد با رابطه مرکز درمانی با موفقیت انجام شد.');
        
        try {
            // آزمایش دریافت مرکز درمانی از قرارداد
            $fetchedMedicalCenter = $fetchedContract->medicalCenter;
            $this->assertNotNull($fetchedMedicalCenter, 'رابطه medicalCenter در مدل Contract مقدار null برگرداند');
            
            \Log::info("مرکز درمانی با موفقیت از قرارداد بازیابی شد: " . $fetchedMedicalCenter->name);
        } catch (\Exception $e) {
            \Log::error('خطا در بازیابی مرکز درمانی از قرارداد: ' . $e->getMessage());
            $this->fail('خطا در بازیابی مرکز درمانی از قرارداد: ' . $e->getMessage());
        }
    }

    /**
     * تست عملکرد رابطه با دیتابیس خالی
     */
    public function test_empty_relationship_handling(): void
    {
        // ایجاد قرارداد بدون مرکز درمانی
        $contract = new Contract();
        $contract->contract_number = 'CNT-EMPTY';
        $contract->title = 'قرارداد تست خالی';
        $contract->contract_type = 'service';
        $contract->vendor_name = 'فروشنده تست';
        $contract->contract_value = 1000000;
        $contract->start_date = now();
        $contract->end_date = now()->addYear();
        $contract->status = 'active';
        // بدون تنظیم medical_center_id
        $contract->save();
        
        // بازیابی قرارداد
        $fetchedContract = Contract::find($contract->id);
        
        // بررسی اینکه مرکز درمانی null باشد
        $this->assertNull($fetchedContract->medicalCenter);
        
        // لاگ برای ردیابی
        \Log::info('تست رابطه خالی با موفقیت انجام شد.');
        
        try {
            // تلاش برای دسترسی به ویژگی‌های null
            $name = optional($fetchedContract->medicalCenter)->name;
            $this->assertNull($name);
            \Log::info('مدیریت صحیح رابطه null با استفاده از optional(): OK');
        } catch (\Exception $e) {
            \Log::error('خطا در مدیریت رابطه null: ' . $e->getMessage());
            $this->fail('خطا در مدیریت رابطه null: ' . $e->getMessage());
        }
    }
}
