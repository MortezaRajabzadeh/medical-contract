<?php

namespace App\Filament\Resources\MedicalCenterResource\Pages;

use App\Filament\Resources\MedicalCenterResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMedicalCenter extends CreateRecord
{
    protected static string $resource = MedicalCenterResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'مرکز درمانی با موفقیت ایجاد شد';
    }
}
