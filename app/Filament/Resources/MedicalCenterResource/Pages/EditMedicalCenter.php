<?php

namespace App\Filament\Resources\MedicalCenterResource\Pages;

use App\Filament\Resources\MedicalCenterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedicalCenter extends EditRecord
{
    protected static string $resource = MedicalCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('حذف مرکز'),
            Actions\ForceDeleteAction::make()->label('حذف کامل'),
            Actions\RestoreAction::make()->label('بازگردانی'),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSavedNotificationTitle(): ?string
    {
        return 'اطلاعات مرکز درمانی با موفقیت به‌روزرسانی شد';
    }
}
