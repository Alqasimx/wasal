<?php

namespace App\Filament\PropertyManagement\Resources\PropertyVendors;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\PropertyManagement\Resources\PropertyVendors\Pages\CreatePropertyVendor;
use App\Filament\PropertyManagement\Resources\PropertyVendors\Pages\EditPropertyVendor;
use App\Filament\PropertyManagement\Resources\PropertyVendors\Pages\ListPropertyVendors;
use App\Models\PropertyVendor;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class PropertyVendorResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = PropertyVendor::class;
    protected static string $viewPermission = 'property_vendors.view';
    protected static string $managePermission = 'property_vendors.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    protected static ?string $navigationLabel = 'الموردون والفنيون';
    protected static ?string $modelLabel = 'مورد / فني';
    protected static ?string $pluralModelLabel = 'الموردون والفنيون';
    protected static string|UnitEnum|null $navigationGroup = 'الخدمات والصيانة';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('الاسم')
                ->required()
                ->maxLength(255),

            TextInput::make('phone')
                ->label('رقم الهاتف')
                ->tel()
                ->maxLength(50),

            TextInput::make('email')
                ->label('البريد الإلكتروني')
                ->email()
                ->maxLength(255),

            Select::make('service_categories')
                ->label('التخصصات')
                ->multiple()
                ->options([
                    'electrical' => 'كهرباء',
                    'plumbing' => 'سباكة',
                    'air_conditioning' => 'تكييف',
                    'solar' => 'طاقة شمسية',
                    'cleaning' => 'نظافة',
                    'pumps' => 'مضخات ومياه',
                    'painting' => 'دهان',
                    'carpentry' => 'نجارة',
                    'elevators' => 'مصاعد',
                    'security' => 'كاميرات وأمن',
                    'network' => 'شبكات وإنترنت',
                    'general' => 'صيانة عامة',
                ])
                ->searchable(),

            Toggle::make('is_active')
                ->label('نشط')
                ->default(true),

            Textarea::make('notes')
                ->label('ملاحظات')
                ->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('service_categories')
                    ->label('التخصصات')
                    ->formatStateUsing(function ($state): string {
                        if (! is_array($state)) {
                            return '—';
                        }

                        $labels = [
                            'electrical' => 'كهرباء',
                            'plumbing' => 'سباكة',
                            'air_conditioning' => 'تكييف',
                            'solar' => 'طاقة شمسية',
                            'cleaning' => 'نظافة',
                            'pumps' => 'مضخات ومياه',
                            'painting' => 'دهان',
                            'carpentry' => 'نجارة',
                            'elevators' => 'مصاعد',
                            'security' => 'كاميرات وأمن',
                            'network' => 'شبكات وإنترنت',
                            'general' => 'صيانة عامة',
                        ];

                        return collect($state)
                            ->map(fn (string $item): string => $labels[$item] ?? $item)
                            ->join('، ');
                    })
                    ->limit(35)
                    ->wrap(),

                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                TextColumn::make('maintenance_requests_count')
                    ->label('طلبات الصيانة')
                    ->state(fn (PropertyVendor $record): int => $record->maintenanceRequests()->count()),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('الحالة')
                    ->options([
                        1 => 'نشط',
                        0 => 'غير نشط',
                    ]),
            ])
            ->striped()
            ->defaultPaginationPageOption(10)
            ->recordActions([
                EditAction::make()->label('تعديل'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPropertyVendors::route('/'),
            'create' => CreatePropertyVendor::route('/create'),
            'edit' => EditPropertyVendor::route('/{record}/edit'),
        ];
    }
}
