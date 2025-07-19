<?php

namespace Tests\Feature;

use Tests\TestCase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\MedicalCenter;
use App\Livewire\Contracts\ContractUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ContractStatusChanged;

class ContractUploadTest extends TestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Fake the storage disk for testing
        Storage::fake('private');
        
        // Fake notifications
        Notification::fake();
        
        // Create test data
        $this->medicalCenter = MedicalCenter::factory()->create();
        $this->adminUser = User::factory()->create([
            'medical_center_id' => $this->medicalCenter->id,
            'user_type' => 'medical_admin'
        ]);
        
        $this->staffUser = User::factory()->create([
            'medical_center_id' => $this->medicalCenter->id,
            'user_type' => 'medical_staff'
        ]);
    }
    
    /** @test */
    public function medical_staff_can_upload_contract()
    {
        $this->actingAs($this->staffUser);
        
        $file = UploadedFile::fake()->create('test_contract.pdf', 1024, 'application/pdf');
        
        Livewire::test(ContractUpload::class)
            ->set('title', 'Annual Maintenance Contract')
            ->set('description', 'Annual maintenance for medical equipment')
            ->set('contractType', 'maintenance')
            ->set('vendorName', 'Medical Equipment Co.')
            ->set('contractValue', 15000.00)
            ->set('startDate', '2024-01-01')
            ->set('endDate', '2024-12-31')
            ->set('contractFile', $file)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('title', '')
            ->assertSessionHas('message', 'Contract uploaded successfully!');
        
        // Assert the contract was stored in the database
        $this->assertDatabaseHas('contracts', [
            'title' => 'Annual Maintenance Contract',
            'contract_type' => 'maintenance',
            'vendor_name' => 'Medical Equipment Co.',
            'contract_value' => 1500000, // Stored in cents
            'status' => 'uploaded',
            'medical_center_id' => $this->medicalCenter->id,
            'created_by' => $this->staffUser->id,
        ]);
        
        // Assert the file was stored
        Storage::disk('private')->assertExists('contracts/' . $file->hashName());
        
        // Assert notification was sent
        Notification::assertSentTo(
            [$this->adminUser],
            ContractStatusChanged::class,
            function ($notification, $channels) {
                return $notification->newStatus === 'uploaded';
            }
        );
    }
    
    /** @test */
    public function contract_requires_valid_file()
    {
        $this->actingAs($this->staffUser);
        
        // Test invalid file type
        $invalidFile = UploadedFile::fake()->create('document.txt', 1024, 'text/plain');
        
        Livewire::test(ContractUpload::class)
            ->set('contractFile', $invalidFile)
            ->call('save')
            ->assertHasErrors(['contractFile' => 'mimetypes']);
        
        // Test file size too large (10MB max)
        $oversizedFile = UploadedFile::fake()->create('large.pdf', 11000, 'application/pdf');
        
        Livewire::test(ContractUpload::class)
            ->set('contractFile', $oversizedFile)
            ->call('save')
            ->assertHasErrors(['contractFile' => 'max']);
    }
    
    /** @test */
    public function contract_requires_all_required_fields()
    {
        $this->actingAs($this->staffUser);
        
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        $testCases = [
            'title' => ['field' => 'title', 'value' => ''],
            'contractType' => ['field' => 'contractType', 'value' => ''],
            'vendorName' => ['field' => 'vendorName', 'value' => ''],
            'contractValue' => ['field' => 'contractValue', 'value' => ''],
            'startDate' => ['field' => 'startDate', 'value' => ''],
            'endDate' => ['field' => 'endDate', 'value' => ''],
            'contractFile' => ['field' => 'contractFile', 'value' => null],
        ];
        
        foreach ($testCases as $field => $data) {
            $test = Livewire::test(ContractUpload::class)
                ->set('title', 'Test Contract')
                ->set('description', 'Test description')
                ->set('contractType', 'service')
                ->set('vendorName', 'Test Vendor')
                ->set('contractValue', 1000.00)
                ->set('startDate', '2024-01-01')
                ->set('endDate', '2024-12-31')
                ->set('contractFile', $file);
                
            $test->set($data['field'], $data['value'])
                 ->call('save')
                 ->assertHasErrors([$data['field'] => 'required']);
        }
    }
    
    /** @test */
    public function end_date_must_be_after_start_date()
    {
        $this->actingAs($this->staffUser);
        
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        Livewire::test(ContractUpload::class)
            ->set('title', 'Test Contract')
            ->set('description', 'Test description')
            ->set('contractType', 'service')
            ->set('vendorName', 'Test Vendor')
            ->set('contractValue', 1000.00)
            ->set('startDate', '2024-12-31')
            ->set('endDate', '2024-01-01')
            ->set('contractFile', $file)
            ->call('save')
            ->assertHasErrors(['endDate' => 'after_or_equal']);
    }
    
    /** @test */
    public function only_authorized_users_can_upload_contracts()
    {
        // Unauthorized user (viewer role)
        $viewerUser = User::factory()->create([
            'medical_center_id' => $this->medicalCenter->id,
            'user_type' => 'viewer'
        ]);
        
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        // Viewer should not be able to access the upload page
        $this->actingAs($viewerUser)
            ->get(route('contracts.upload'))
            ->assertForbidden();
            
        // Viewer should not be able to submit the form
        $this->actingAs($viewerUser)
            ->post(route('contracts.store'), [
                'title' => 'Test Contract',
                'contract_type' => 'service',
                'vendor_name' => 'Test Vendor',
                'contract_value' => 1000.00,
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ])
            ->assertForbidden();
    }
}
