<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Contract;
use App\Models\MedicalCenter;
use App\Filament\Resources\ContractResource;
use Filament\Forms\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class ContractSelectFieldTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_load_medical_centers_in_select_field()
    {
        // ایجاد چند مرکز درمانی برای تست
        $medicalCenters = MedicalCenter::factory()->count(3)->create();
        
        try {
            // آماده‌سازی فرم به صورت مستقل
            $form = $this->getFormInstance();
            
            // بررسی وجود فیلد medical_center_id در فرم
            $this->assertTrue($form->hasComponent('medical_center_id'));
            
            // بررسی اینکه فیلد از نوع Select باشد
            $selectField = $form->getComponent('medical_center_id');
            $this->assertInstanceOf(\Filament\Forms\Components\Select::class, $selectField);
            
            // تست اینکه آپشن‌ها به درستی بارگذاری شوند
            $options = $selectField->getOptions();
            $this->assertCount(3, $options);
            
            // تست جستجو
            $searchResults = $selectField->getSearchResultsUsing()('test');
            $this->assertIsArray($searchResults);
            
            $this->addToLog('تست فیلد Select مرکز درمانی با موفقیت انجام شد');
        } catch (\Exception $e) {
            $this->addToLog('خطا در تست فیلد Select: ' . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /** @test */
    public function it_can_create_new_medical_center_from_form()
    {
        $newMedicalCenterData = [
            'name' => 'مرکز درمانی جدید',
            'license_number' => 'TEST-123',
            'phone' => '09123456789',
            'email' => 'test@example.com'
        ];
        
        try {
            // آماده‌سازی فرم
            $form = $this->getFormInstance();
            $selectField = $form->getComponent('medical_center_id');
            
            // تست متد createOptionUsing
            $createOptionMethod = $selectField->getCreateOptionUsing();
            $newId = $createOptionMethod($newMedicalCenterData);
            
            // بررسی اینکه آیا رکورد جدید در دیتابیس ایجاد شده است
            $this->assertDatabaseHas('medical_centers', [
                'id' => $newId,
                'name' => 'مرکز درمانی جدید'
            ]);
            
            $this->addToLog('تست ایجاد مرکز درمانی جدید از فرم با موفقیت انجام شد');
        } catch (\Exception $e) {
            $this->addToLog('خطا در تست ایجاد مرکز درمانی: ' . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * ایجاد نمونه فرم برای تست
     */
    private function getFormInstance(): Form
    {
        $livewire = Livewire::test(ContractResource\Pages\CreateContract::class);
        return ContractResource::form(new Form($livewire));
    }
    
    /**
     * افزودن لاگ با فرمت استاندارد برای قابلیت ردیابی
     */
    private function addToLog(string $message, string $level = 'info'): void
    {
        $context = [
            'test' => get_class($this) . '::' . debug_backtrace()[1]['function'],
            'timestamp' => now()->toDateTimeString()
        ];
        
        logger()->$level($message, $context);
    }
}
