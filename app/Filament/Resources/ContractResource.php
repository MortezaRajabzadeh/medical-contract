<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Models\Contract;
use App\Models\MedicalCenter;
use App\Repositories\Interfaces\ContractRepositoryInterface;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Filament\Support\Colors;
use Filament\Tables\Actions\Action;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'مدیریت قراردادها';
    protected static ?int $navigationSort = 1;
    
    protected static ?string $modelLabel = 'قرارداد';
    protected static ?string $pluralModelLabel = 'قراردادها';
    
    public static function getCreateButtonLabel(): string
    {
        return 'قرارداد جدید';
    }

    public static function getNavigationLabel(): string
    {
        return 'قراردادها';
    }
    
    /**
     * بهینه‌سازی N+1 Query با eager loading رابطه‌ها
     */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['medicalCenter', 'createdBy', 'approvedBy', 'viewedBy']);
    }

    public static function form(Form $form): Form
    {
        try {
            // اضافه کردن لاگ برای ردیابی روند ایجاد فرم
            \Illuminate\Support\Facades\Log::info('ContractResource::form - شروع ساخت فرم قرارداد', [
                'user_id' => auth()->id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ]);
        return $form
            ->schema([
                Section::make('اطلاعات قرارداد')
                    ->schema([
                        TextInput::make('title')
                            ->label('عنوان')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                            
                        Select::make('medical_center_id')
                            ->label('مرکز درمانی')
                            ->options(\App\Models\MedicalCenter::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getSearchResultsUsing(function (string $search): array {
                                return \App\Models\MedicalCenter::where('name', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(fn ($value): ?string => 
                                \App\Models\MedicalCenter::find($value)?->name
                            )
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('نام مرکز درمانی')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('license_number')
                                    ->label('شماره مجوز')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label('شماره تماس')
                                    ->tel()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('ایمیل')
                                    ->email()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return \App\Models\MedicalCenter::create($data)->getKey();
                            }),
                            
                        Select::make('contract_type')
                            ->label('نوع قرارداد')
                            ->options([
                                'service' => 'خدمات',
                                'equipment' => 'تجهیزات',
                                'pharmaceutical' => 'دارویی',
                                'maintenance' => 'نگهداری',
                                'consulting' => 'مشاوره',
                            ])
                            ->required(),
                            
                        TextInput::make('vendor_name')
                            ->label('نام فروشنده')
                            ->required()
                            ->maxLength(255),
                            
                        TextInput::make('contract_number')
                            ->label('شماره قرارداد')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                            
                        TextInput::make('contract_value')
                            ->label('مبلغ قرارداد')
                            ->numeric()
                            ->required()
                            ->prefix('ریال'),
                            
                        DatePicker::make('start_date')
                            ->label('تاریخ شروع')
                            ->required(),
                            
                        DatePicker::make('end_date')
                            ->label('تاریخ پایان')
                            ->required()
                            ->after('start_date'),
                            
                        Select::make('status')
                            ->label('وضعیت')
                            ->options([
                                'pending' => 'در انتظار',
                                'uploaded' => 'بارگذاری شده',
                                'approved' => 'تایید شده',
                                'active' => 'فعال',
                                'expired' => 'منقضی شده',
                                'terminated' => 'لغو شده',
                            ])
                            ->required(),
                            
                        Textarea::make('description')
                            ->label('توضیحات')
                            ->required()
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                    
                Section::make('سند قرارداد')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('سند')
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240)
                            ->directory('private/contracts')
                            ->visibility('private')
                            ->downloadable()
                            ->openable()
                            ->previewable()
                            ->columnSpanFull(),
                            
                        TextInput::make('original_filename')
                            ->label('نام فایل اصلی')
                            ->disabled()
                            ->dehydrated(false),
                            
                        TextInput::make('file_size')
                            ->label('حجم فایل (KB)')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                            
                        TextInput::make('file_hash')
                            ->label('هش فایل')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(3)
                    ->collapsible(),
                    
                Section::make('اطلاعات حسابرسی')
                    ->schema([
                        TextInput::make('created_by_name')
                            ->label('ایجادکننده')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($record) => $record?->createdBy?->name ?? '-'),
                            
                        TextInput::make('approved_by_name')
                            ->label('تاییدکننده')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($record) => $record?->approvedBy?->name ?? '-'),
                            
                        TextInput::make('approved_at')
                            ->label('تاریخ تایید')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->visible(fn ($record) => $record !== null),
            ]);
        } catch (\Exception $e) {
            // لاگ خطا برای ردیابی مشکلات احتمالی
            \Illuminate\Support\Facades\Log::error('ContractResource::form - خطا در ساخت فرم قرارداد', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ]);
            
            throw $e; // انتشار خطا برای مدیریت در سطح بالاتر
        }
    }

    public static function table(Table $table): Table
    {
        try {
            // اضافه کردن لاگ برای ردیابی روند ساخت جدول
            \Illuminate\Support\Facades\Log::info('ContractResource::table - شروع ساخت جدول قراردادها', [
                'user_id' => auth()->id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ]);
        return $table
            ->filters([
                Filter::make('is_signed')
                    ->label('امضا شده')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('signed_file_path')),
                    
                Filter::make('not_viewed')
                    ->label('مشاهده نشده توسط من')
                    ->query(function (Builder $query): Builder {
                        return $query->whereDoesntHave('viewedBy', function($query) {
                            $query->where('users.id', auth()->id());
                        });
                    }),
                    
                SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'pending' => 'در انتظار',
                        'approved' => 'تایید شده',
                        'active' => 'فعال',
                        'SIGNED' => 'امضا شده',
                        'terminated' => 'لغو شده',
                    ]),
                    
                Filter::make('start_date')
                    ->label('تاریخ شروع')
                    ->form([
                        Forms\Components\DatePicker::make('start_date_from')
                            ->label('از تاریخ')
                            ->timezone('Asia/Tehran'),
                        Forms\Components\DatePicker::make('start_date_until')
                            ->label('تا تاریخ')
                            ->timezone('Asia/Tehran'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['start_date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '<=', $date),
                            );
                    }),
                    
                Filter::make('end_date')
                    ->label('تاریخ پایان')
                    ->form([
                        Forms\Components\DatePicker::make('end_date_from')
                            ->label('از تاریخ')
                            ->timezone('Asia/Tehran'),
                        Forms\Components\DatePicker::make('end_date_until')
                            ->label('تا تاریخ')
                            ->timezone('Asia/Tehran'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['end_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('end_date', '>=', $date),
                            )
                            ->when(
                                $data['end_date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date),
                            );
                    }),
                    
                Filter::make('signed_date')
                    ->label('تاریخ امضا')
                    ->form([
                        Forms\Components\DatePicker::make('signed_date_from')
                            ->label('از تاریخ')
                            ->timezone('Asia/Tehran'),
                        Forms\Components\DatePicker::make('signed_date_until')
                            ->label('تا تاریخ')
                            ->timezone('Asia/Tehran'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['signed_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('signed_date', '>=', $date),
                            )
                            ->when(
                                $data['signed_date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('signed_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                // اضافه کردن دکمه مشاهده فایل امضا شده
                Tables\Actions\Action::make('viewSignedFile')
                    ->label('مشاهده فایل')
                    ->icon('heroicon-o-document')
                    ->color('success')
                    ->url(fn ($record) => $record && $record->signed_file_path ? Storage::url($record->signed_file_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record && !empty($record->signed_file_path))
            ])
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('medicalCenter.name')
                    ->label('مرکز درمانی')
                    ->searchable()
                    ->sortable(),
                    
                BadgeColumn::make('status')
                    ->label('وضعیت')
                    ->sortable()
                    ->colors([
                        'primary' => 'active',
                        'success' => ['approved', 'SIGNED'],
                        'warning' => 'pending',
                        'danger' => 'terminated',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => ['approved', 'SIGNED'],
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-document' => 'active',
                        'heroicon-o-x-circle' => 'terminated',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'pending' => 'در انتظار',
                        'approved' => 'تایید شده',
                        'active' => 'فعال',
                        'terminated' => 'لغو شده',
                        'SIGNED' => 'امضا شده',
                        default => $state,
                    }),
                
                IconColumn::make('signed_file_path')
                    ->label('فایل امضا شده')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-document')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(function ($record) {
                        if (!$record) return 'اطلاعات موجود نیست';
                        return $record->signed_file_path ? 'فایل امضا شده موجود است' : 'فایل امضا شده موجود نیست';
                    }),
                
                IconColumn::make('is_viewed_by_admin')
                    ->label('مشاهده شده')
                    ->state(function ($record): bool {
                        if (!$record) return false;
                        return $record->viewedBy && $record->viewedBy->contains(auth()->user());
                    })
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(function ($record) {
                        if (!$record) return 'اطلاعات موجود نیست';
                        return $record->viewedBy && $record->viewedBy->contains(auth()->user()) 
                            ? 'مشاهده شده توسط شما' 
                            : 'هنوز مشاهده نشده';
                    })
                    ->alignCenter(),
                
                ViewColumn::make('signed_file_preview')
                    ->label('پیش‌نمایش')
                    ->view('filament.tables.columns.pdf-preview-column')
                    ->tooltip('مشاهده پیش‌نمایش فایل امضا شده')
                    ->visible(function ($record): bool {
                        if (!$record) return false;
                        return !empty($record->signed_file_path);
                    }),
                
                BadgeColumn::make('contract_type')
                    ->label('نوع قرارداد')
                    ->colors([
                        'primary' => 'service',
                        'success' => 'equipment',
                        'warning' => 'pharmaceutical',
                        'danger' => 'maintenance',
                        'gray' => 'consulting',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'service' => 'خدمات',
                        'equipment' => 'تجهیزات',
                        'pharmaceutical' => 'دارویی',
                        'maintenance' => 'نگهداری',
                        'consulting' => 'مشاوره',
                        default => $state,
                    }),
                
                TextColumn::make('start_date')
                    ->label('تاریخ شروع')
                    ->date()
                    ->sortable(),
                    
                TextColumn::make('end_date')
                    ->label('تاریخ پایان')
                    ->date()
                    ->sortable()
                    ->color(function ($record) {
                        if (!$record || !$record->end_date) return 'primary';
                        return $record->end_date->isPast() ? 'danger' : 'primary';
                    }),
                
                TextColumn::make('signed_date')
                    ->label('تاریخ امضا')
                    ->date()
                    ->sortable()
                    ->visible(function ($record): bool {
                        if (!$record) return false;
                        return !empty($record->signed_date);
                    }),
                    
                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('createdBy.name')
                    ->label('ایجاد کننده')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('is_signed')
                    ->label('امضا شده')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('signed_file_path')),
                
                Filter::make('not_viewed')
                    ->label('مشاهده نشده توسط من')
                    ->query(function (Builder $query): Builder {
                        return $query->whereDoesntHave('viewedBy', function($query) {
                            $query->where('users.id', auth()->id());
                        });
                    }),
                
                SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'pending' => 'در انتظار',
                        'uploaded' => 'بارگذاری شده',
                        'approved' => 'تایید شده',
                        'active' => 'فعال',
                        'expired' => 'منقضی شده',
                        'terminated' => 'لغو شده',
                    ]),
                    
                SelectFilter::make('contract_type')
                    ->label('نوع قرارداد')
                    ->multiple()
                    ->options([
                        'service' => 'خدمات',
                        'equipment' => 'تجهیزات',
                        'pharmaceutical' => 'دارویی',
                        'maintenance' => 'نگهداری',
                        'consulting' => 'مشاوره',
                    ]),
                    
                Filter::make('expiring_soon')
                    ->label('انقضا در ۳۰ روز آینده')
                    ->indicateUsing(fn () => 'انقضا در ۳۰ روز آینده')
                    ->query(fn (Builder $query): Builder => $query->where('end_date', '<=', now()->addDays(30))
                        ->where('end_date', '>=', now())
                        ->whereIn('status', ['active', 'approved'])),
                        
                Filter::make('expired')
                    ->label('منقضی شده')
                    ->indicateUsing(fn () => 'منقضی شده')
                    ->query(fn (Builder $query): Builder => $query->where('end_date', '<', now())
                        ->whereIn('status', ['active', 'approved'])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('نمایش'),
                Tables\Actions\EditAction::make()->label('ویرایش'),
                Tables\Actions\DeleteAction::make()->label('حذف')->iconPosition('after'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف گروهی'),
                ])->label('عملیات گروهی'),
            ])
            ->defaultSort('end_date', 'asc')
            ->defaultPaginationPageOption(10)
            ->recordUrl(fn (Contract $record): string => route('filament.admin.resources.contracts.view', $record));
        } catch (\Exception $e) {
            // لاگ خطا برای ردیابی مشکلات احتمالی
            \Illuminate\Support\Facades\Log::error('ContractResource::table - خطا در ساخت جدول قراردادها', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id() ?? 'مهمان',
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ]);
            
            throw $e; // انتشار خطا برای مدیریت در سطح بالاتر
        }
    }
    
    public static function getRelations(): array
    {
        return [
            // Add any relations here if needed
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\ContractResource\Pages\ListContracts::route('/'),
            'create' => \App\Filament\Resources\ContractResource\Pages\CreateContract::route('/create'),
            'view' => \App\Filament\Resources\ContractResource\Pages\ViewContract::route('/{record}'),
            'edit' => \App\Filament\Resources\ContractResource\Pages\EditContract::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    
    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }
    
    /**
     * Get a clean option string for display in select fields.
     *
     * @param \App\Models\MedicalCenter $medicalCenter
     * @return string
     */
    protected static function getCleanOptionString(\App\Models\MedicalCenter $medicalCenter): string
    {
        return $medicalCenter->name;
    }
}
