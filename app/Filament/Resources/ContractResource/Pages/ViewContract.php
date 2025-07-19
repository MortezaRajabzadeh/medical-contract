<?php

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use App\Models\Contract;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ViewContract extends ViewRecord
{
    protected static string $resource = ContractResource::class;
    
    /**
     * اضافه کردن اکشن های سربرگ
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            // اضافه کردن اکشن مشاهده فایل امضا شده
            Action::make('viewSignedFile')
                ->label('مشاهده فایل امضا شده')
                ->icon('heroicon-o-document')
                ->color('success')
                ->url(function() {
                    if (!$this->record->signed_file_path) {
                        return null;
                    }
                    
                    return Storage::url($this->record->signed_file_path);
                })
                ->openUrlInNewTab()
                ->visible(fn() => !empty($this->record->signed_file_path)),
                
            // اضافه کردن اکشن تغییر وضعیت
            Action::make('changeStatus')
                ->label('تغییر وضعیت')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\Select::make('status')
                        ->label('وضعیت جدید')
                        ->options([
                            'pending' => 'در انتظار',
                            'approved' => 'تایید شده',
                            'active' => 'فعال',
                            'SIGNED' => 'امضا شده',
                            'terminated' => 'لغو شده',
                        ])
                        ->default(fn() => $this->record->status)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    try {
                        $this->record->status = $data['status'];
                        $this->record->save();
                        
                        Log::info('وضعیت قرارداد تغییر کرد', [
                            'contract_id' => $this->record->id,
                            'new_status' => $data['status'],
                            'user_id' => Auth::id()
                        ]);
                        
                        Notification::make()
                            ->title('وضعیت قرارداد با موفقیت تغییر کرد')
                            ->success()
                            ->send();
                            
                    } catch (\Exception $e) {
                        Log::error('خطا در تغییر وضعیت قرارداد', [
                            'contract_id' => $this->record->id,
                            'error' => $e->getMessage()
                        ]);
                        
                        Notification::make()
                            ->title('خطا در تغییر وضعیت')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                            
                        throw new Halt();
                    }
                }),
        ];
    }
    
    /**
     * هنگام بارگذاری صفحه، مشاهده قرارداد را ثبت می‌کند
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // اضافه کردن کاربر جاری به لیست مشاهده کنندگان
        if (!$this->record->viewedBy->contains(Auth::user())) {
            $this->record->viewedBy()->attach(Auth::user()->id);
            
            Log::info('قرارداد توسط ادمین مشاهده شد', [
                'contract_id' => $this->record->id,
                'user_id' => Auth::id()
            ]);
        }
        
        return $data;
    }
}
