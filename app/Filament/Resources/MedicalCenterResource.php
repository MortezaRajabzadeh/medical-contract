<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicalCenterResource\Pages;
use App\Models\MedicalCenter;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MedicalCenterResource extends Resource
{
    protected static ?string $model = MedicalCenter::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    
    // نام نمایشی منبع در پنل به فارسی
    protected static ?string $label = 'مرکز درمانی';
    protected static ?string $pluralLabel = 'مراکز درمانی';

    public static function getNavigationGroup(): ?string
    {
        return 'مدیریت مراکز درمانی';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('نام مرکز درمانی')
                    ->required()
                    ->maxLength(255),
                
                TextInput::make('code')
                    ->label('کد مرکز')
                    ->unique(ignorable: fn ($record) => $record)
                    ->required()
                    ->maxLength(50),
                
                TextInput::make('phone')
                    ->label('تلفن')
                    ->tel()
                    ->maxLength(20),
                
                TextInput::make('email')
                    ->label('ایمیل')
                    ->email()
                    ->maxLength(255),
                
                Textarea::make('address')
                    ->label('آدرس')
                    ->maxLength(500)
                    ->columnSpanFull(),
                
                Select::make('user_id')
                    ->label('کاربر مسئول')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('نام کامل')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('ایمیل')
                            ->email()
                            ->unique()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label('رمز عبور')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->maxLength(255),
                    ])
                    ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                        return $action
                            ->modalHeading('ایجاد کاربر جدید')
                            ->modalSubmitActionLabel('ایجاد کاربر');
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('نام مرکز')
                    ->searchable(),
                
                TextColumn::make('code')
                    ->label('کد مرکز')
                    ->searchable(),
                
                TextColumn::make('phone')
                    ->label('تلفن')
                    ->searchable(),
                
                TextColumn::make('email')
                    ->label('ایمیل')
                    ->searchable(),
                
                TextColumn::make('user.name')
                    ->label('کاربر مسئول')
                    ->searchable(),
                
                TextColumn::make('contracts_count')
                    ->label('تعداد قراردادها')
                    ->counts('contracts'),
                
                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime('Y/m/d H:i'),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_contracts')
                    ->label('دارای قرارداد')
                    ->query(fn (Builder $query): Builder => $query->whereHas('contracts')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedicalCenters::route('/'),
            'create' => Pages\CreateMedicalCenter::route('/create'),
            'edit' => Pages\EditMedicalCenter::route('/{record}/edit'),
        ];
    }
}
