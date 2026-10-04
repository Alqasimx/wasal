<?php

namespace App\Filament\PropertyManagement\Resources\PropertyExpenses;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\PropertyManagement\Resources\PropertyExpenses\Pages\CreatePropertyExpense;
use App\Filament\PropertyManagement\Resources\PropertyExpenses\Pages\EditPropertyExpense;
use App\Filament\PropertyManagement\Resources\PropertyExpenses\Pages\ListPropertyExpenses;
use App\Models\PropertyExpense;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PropertyExpenseResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = PropertyExpense::class;
    protected static string $viewPermission = 'property_expenses.view';
    protected static string $managePermission = 'property_expenses.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'المصروفات';
    protected static ?string $modelLabel = 'مصروف';
    protected static ?string $pluralModelLabel = 'المصروفات';
    protected static string|UnitEnum|null $navigationGroup = 'الملاك والتسويات';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_id')
                ->label('العقار')
                ->relationship('property', 'internal_code')
                ->getOptionLabelFromRecordUsing(fn ($record): string => $record->internal_code.' — '.$record->title_ar)
                ->searchable(['internal_code', 'title_ar'])
                ->preload()
                ->required(),

            Select::make('property_unit_id')
                ->label('الوحدة')
                ->relationship('unit', 'code')
                ->searchable()
                ->preload(),

            Select::make('property_management_agreement_id')
                ->label('اتفاق الإدارة')
                ->relationship('agreement', 'id')
                ->getOptionLabelFromRecordUsing(fn ($record): string =>
                    '#'.$record->id.' — '.($record->property?->internal_code ?? 'عقار'))
                ->searchable()
                ->preload(),

            Select::make('maintenance_request_id')
                ->label('طلب الصيانة المرتبط')
                ->relationship('maintenanceRequest', 'reference_number')
                ->searchable()
                ->preload(),

            Select::make('property_vendor_id')
                ->label('المورد / الفني')
                ->relationship('vendor', 'name')
                ->searchable()
                ->preload(),

            Select::make('category')
                ->label('نوع المصروف')
                ->options([
                    'maintenance' => 'صيانة',
                    'cleaning' => 'نظافة',
                    'utilities' => 'مرافق',
                    'supplies' => 'مستلزمات',
                    'fees' => 'رسوم',
                    'taxes' => 'ضرائب',
                    'management' => 'إدارة',
                    'other' => 'أخرى',
                ])
                ->required(),

            TextInput::make('description')
                ->label('وصف المصروف')
                ->required()
                ->maxLength(255),

            TextInput::make('amount')
                ->label('إجمالي المصروف')
                ->numeric()
                ->minValue(0)
                ->required(),

            TextInput::make('paid_amount')
                ->label('المبلغ المسدد')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->required(),

            Select::make('currency_id')
                ->label('العملة')
                ->relationship('currency', 'name_ar')
                ->searchable()
                ->preload()
                ->required(),

            DatePicker::make('incurred_at')
                ->label('تاريخ المصروف')
                ->default(today())
                ->required(),

            Select::make('cost_bearer')
                ->label('من يتحمل المصروف')
                ->options([
                    'owner' => 'المالك',
                    'tenant' => 'المستأجر',
                    'wasal' => 'وصال',
                    'shared' => 'مشترك',
                ])
                ->default('owner')
                ->required(),

            TextInput::make('reference_number')
                ->label('رقم الفاتورة / المرجع')
                ->maxLength(255),

            FileUpload::make('attachment_path')
                ->label('الفاتورة / المرفق')
                ->disk('public')
                ->directory('property-management/expenses')
                ->downloadable()
                ->openable(),

            Textarea::make('notes')
                ->label('ملاحظات')
                ->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('incurred_at', 'desc')
            ->columns([
                TextColumn::make('incurred_at')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('property.internal_code')
                    ->label('العقار')
                    ->searchable(),

                TextColumn::make('unit.code')
                    ->label('الوحدة')
                    ->placeholder('—'),

                TextColumn::make('category')
                    ->label('النوع')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'maintenance' => 'صيانة',
                        'cleaning' => 'نظافة',
                        'utilities' => 'مرافق',
                        'supplies' => 'مستلزمات',
                        'fees' => 'رسوم',
                        'taxes' => 'ضرائب',
                        'management' => 'إدارة',
                        default => 'أخرى',
                    })
                    ->badge(),

                TextColumn::make('description')
                    ->label('البيان')
                    ->searchable()
                    ->limit(32),

                TextColumn::make('amount')
                    ->label('الإجمالي')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('المسدد')
                    ->numeric(),

                TextColumn::make('remaining_amount')
                    ->label('المتبقي')
                    ->state(fn (PropertyExpense $record): float => max(
                        0,
                        (float) $record->amount - (float) $record->paid_amount
                    ))
                    ->numeric(),

                TextColumn::make('currency.code')
                    ->label('العملة'),

                TextColumn::make('payment_status')
                    ->label('السداد')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        PropertyExpense::STATUS_PAID => 'مسدد',
                        PropertyExpense::STATUS_PARTIAL => 'جزئي',
                        default => 'غير مسدد',
                    })
                    ->badge(),

                TextColumn::make('vendor.name')
                    ->label('المورد')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('حالة السداد')
                    ->options([
                        PropertyExpense::STATUS_UNPAID => 'غير مسدد',
                        PropertyExpense::STATUS_PARTIAL => 'جزئي',
                        PropertyExpense::STATUS_PAID => 'مسدد',
                    ]),

                SelectFilter::make('cost_bearer')
                    ->label('متحمل المصروف')
                    ->options([
                        'owner' => 'المالك',
                        'tenant' => 'المستأجر',
                        'wasal' => 'وصال',
                        'shared' => 'مشترك',
                    ]),

                SelectFilter::make('property_id')
                    ->label('العقار')
                    ->relationship('property', 'internal_code')
                    ->searchable()
                    ->preload(),
            ])
            ->striped()
            ->defaultPaginationPageOption(10)
            ->recordActions([
                EditAction::make()->label('تعديل'),
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
            'index' => ListPropertyExpenses::route('/'),
            'create' => CreatePropertyExpense::route('/create'),
            'edit' => EditPropertyExpense::route('/{record}/edit'),
        ];
    }
}
