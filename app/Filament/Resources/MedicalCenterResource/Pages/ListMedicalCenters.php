<?php

namespace App\Filament\Resources\MedicalCenterResource\Pages;

use App\Filament\Resources\MedicalCenterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedicalCenters extends ListRecords
{
    protected static string $resource = MedicalCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('ایجاد مرکز درمانی'),
        ];
    }
}
