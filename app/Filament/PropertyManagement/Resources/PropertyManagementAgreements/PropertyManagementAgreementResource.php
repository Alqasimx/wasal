<?php

namespace App\Filament\PropertyManagement\Resources\PropertyManagementAgreements;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\PropertyManagement\Pages\PropertyOverview;
use App\Filament\PropertyManagement\Resources\PropertyManagementAgreements\Pages\CreatePropertyManagementAgreement;
use App\Filament\PropertyManagement\Resources\PropertyManagementAgreements\Pages\EditPropertyManagementAgreement;
use App\Filament\PropertyManagement\Resources\PropertyManagementAgreements\Pages\ListPropertyManagementAgreements;
use App\Models\PropertyManagementAgreement;
use App\Models\PropertyService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PropertyManagementAgreementResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = PropertyManagementAgreement::class;
    protected static string $viewPermission = 'property_management_agreements.view';
    protected static string $managePermission = 'property_management_agreements.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    protected static ?string $navigationLabel = 'العقارات المُدارة';
    protected static ?string $modelLabel = 'اتفاق إدارة عقار';
    protected static ?string $pluralModelLabel = 'العقارات المُدارة';
    protected static string|UnitEnum|null $navigationGroup = 'الأصول والإشغال';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('agreement_number')
                ->label('رقم اتفاق الإدارة')
                ->disabled()
                ->dehydrated(false)
                ->placeholder('يُنشأ تلقائيًا عند الحفظ'),

            Select::make('property_id')
                ->label('العقار')
                ->relationship('property', 'internal_code')
                ->getOptionLabelFromRecordUsing(
                    fn ($record): string => $record->internal_code.' — '.$record->title_ar
                )
                ->searchable(['internal_code', 'title_ar'])
                ->preload()
                ->required(),

            Select::make('property_owner_id')
                ->label('المالك')
                ->relationship('propertyOwner', 'id')
                ->getOptionLabelFromRecordUsing(
                    fn ($record): string => $record->external_owner_name
                        ?: ($record->user?->name ?? 'مالك #'.$record->id)
                )
                ->searchable()
                ->preload(),

            Select::make('assigned_manager_user_id')
                ->label('مسؤول إدارة العقار')
                ->relationship('manager', 'name')
                ->searchable()
                ->preload(),

            DatePicker::make('starts_at')
                ->label('بداية الإدارة')
                ->required(),

            DatePicker::make('ends_at')
                ->label('نهاية الإدارة')
                ->afterOrEqual('starts_at'),

            Select::make('status')
                ->label('الحالة')
                ->options([
                    PropertyManagementAgreement::STATUS_ACTIVE => 'نشط',
                    PropertyManagementAgreement::STATUS_PAUSED => 'موقوف مؤقتًا',
                    PropertyManagementAgreement::STATUS_ENDED => 'منتهي',
                ])
                ->default(PropertyManagementAgreement::STATUS_ACTIVE)
                ->required(),

            Select::make('management_fee_type')
                ->label('طريقة رسوم الإدارة')
                ->options([
                    PropertyManagementAgreement::FEE_PERCENTAGE => 'نسبة مئوية',
                    PropertyManagementAgreement::FEE_FIXED => 'مبلغ ثابت',
                ])
                ->default(PropertyManagementAgreement::FEE_PERCENTAGE)
                ->required(),

            TextInput::make('management_fee_value')
                ->label('قيمة رسوم الإدارة')
                ->numeric()
                ->minValue(0)
                ->required(),

            Select::make('fee_billing_frequency')
                ->label('دورية احتساب رسوم الإدارة')
                ->options([
                    PropertyManagementAgreement::BILLING_MONTHLY => 'شهري',
                    PropertyManagementAgreement::BILLING_QUARTERLY => 'ربع سنوي',
                    PropertyManagementAgreement::BILLING_SEMIANNUAL => 'نصف سنوي',
                    PropertyManagementAgreement::BILLING_ANNUAL => 'سنوي',
                ])
                ->default(PropertyManagementAgreement::BILLING_MONTHLY)
                ->required(),

            Select::make('currency_id')
                ->label('عملة الرسوم الثابتة')
                ->relationship('currency', 'name_ar')
                ->searchable()
                ->preload(),

            Toggle::make('auto_renew')
                ->label('تجديد الاتفاق تلقائيًا')
                ->default(false),

            TextInput::make('renewal_notice_days')
                ->label('التنبيه قبل نهاية الاتفاق بالأيام')
                ->numeric()
                ->minValue(0)
                ->default(30)
                ->required(),

            Select::make('included_services')
                ->label('الخدمات المشمولة')
                ->multiple()
                ->options(fn (): array => PropertyService::query()
                    ->where('is_active', true)
                    ->orderBy('name_ar')
                    ->pluck('name_ar', 'id')
                    ->all())
                ->searchable()
                ->preload(),

            FileUpload::make('agreement_document_path')
                ->label('نسخة اتفاق الإدارة')
                ->disk('public')
                ->directory('property-management/agreements')
                ->downloadable()
                ->openable(),

            Textarea::make('notes')
                ->label('ملاحظات الاتفاق')
                ->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                TextColumn::make('agreement_number')
                    ->label('رقم الاتفاق')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),

                TextColumn::make('property.internal_code')
                    ->label('رمز العقار')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('property.title_ar')
                    ->label('العقار')
                    ->searchable()
                    ->limit(28),

                TextColumn::make('owner_name')
                    ->label('المالك')
                    ->state(fn (PropertyManagementAgreement $record): string =>
                        $record->propertyOwner?->external_owner_name
                        ?: ($record->propertyOwner?->user?->name ?? '—')),

                TextColumn::make('occupancy')
                    ->label('الإشغال')
                    ->state(function (PropertyManagementAgreement $record): string {
                        $total = $record->unitsCount();
                        $occupied = $record->activeUnitsCount();

                        return $occupied.'/'.$total.' ('.$record->occupancyPercentage().'%)';
                    })
                    ->badge(),

                TextColumn::make('manager.name')
                    ->label('المسؤول')
                    ->placeholder('غير معين'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        PropertyManagementAgreement::STATUS_PAUSED => 'موقوف مؤقتًا',
                        PropertyManagementAgreement::STATUS_ENDED => 'منتهي',
                        default => 'نشط',
                    })
                    ->badge()
                    ->sortable(),

                TextColumn::make('management_fee_value')
                    ->label('رسوم الإدارة')
                    ->formatStateUsing(fn ($state, PropertyManagementAgreement $record): string =>
                        $record->management_fee_type === PropertyManagementAgreement::FEE_PERCENTAGE
                            ? $state.'%'
                            : number_format((float) $state, 2).' '.($record->currency?->code ?? '')),

                TextColumn::make('services_count')
                    ->label('الخدمات')
                    ->state(fn (PropertyManagementAgreement $record): int =>
                        count($record->included_services ?? []))
                    ->badge(),

                IconColumn::make('auto_renew')
                    ->label('تجديد تلقائي')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('starts_at')
                    ->label('البداية')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('النهاية')
                    ->date('Y-m-d')
                    ->placeholder('مفتوح')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        PropertyManagementAgreement::STATUS_ACTIVE => 'نشط',
                        PropertyManagementAgreement::STATUS_PAUSED => 'موقوف مؤقتًا',
                        PropertyManagementAgreement::STATUS_ENDED => 'منتهي',
                    ]),

                SelectFilter::make('assigned_manager_user_id')
                    ->label('مسؤول الإدارة')
                    ->relationship('manager', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->striped()
            ->defaultPaginationPageOption(10)
            ->recordActions([
                Action::make('property_overview')
                    ->label('ملف العقار')
                    ->icon('heroicon-o-building-office')
                    ->color('primary')
                    ->url(fn (PropertyManagementAgreement $record): string => PropertyOverview::getUrl(
                        ['record' => $record->property_id],
                        panel: 'property-management',
                    )),
                EditAction::make()->label('تعديل الاتفاق'),
            ]);
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPropertyManagementAgreements::route('/'),
            'create' => CreatePropertyManagementAgreement::route('/create'),
            'edit' => EditPropertyManagementAgreement::route('/{record}/edit'),
        ];
    }
}
