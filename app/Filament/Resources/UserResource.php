<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    // نام نمایشی منبع در پنل به فارسی
    protected static ?string $label = 'کاربر';
    protected static ?string $pluralLabel = 'کاربران';

    public static function getNavigationGroup(): ?string
    {
        return 'مدیریت کاربران';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('نام کامل')
                    ->required()
                    ->maxLength(255),
                
                TextInput::make('email')
                    ->label('ایمیل')
                    ->email()
                    ->unique(ignorable: fn ($record) => $record)
                    ->required()
                    ->maxLength(255),
                
                TextInput::make('password')
                    ->label('رمز عبور')
                    ->password()
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                
                Select::make('roles')
                    ->label('نقش‌ها')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('نام')
                    ->searchable(),
                
                TextColumn::make('email')
                    ->label('ایمیل')
                    ->searchable(),
                
                TextColumn::make('roles.name')
                    ->label('نقش‌ها')
                    ->searchable(),
                
                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime('Y/m/d H:i'),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_medical_center')
                    ->label('دارای مرکز درمانی')
                    ->query(fn (Builder $query): Builder => $query->whereHas('medicalCenter')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
