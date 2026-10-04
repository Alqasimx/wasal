<?php

namespace App\Filament\PropertyManagement\Resources\OwnerSettlements;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\PropertyManagement\Resources\OwnerSettlements\Pages\CreateOwnerSettlement;
use App\Filament\PropertyManagement\Resources\OwnerSettlements\Pages\ListOwnerSettlements;
use App\Models\OwnerSettlement;
use App\Services\OwnerSettlementService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
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

class OwnerSettlementResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = OwnerSettlement::class;
    protected static string $viewPermission = 'owner_settlements.view';
    protected static string $managePermission = 'owner_settlements.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static ?string $navigationLabel = 'تسويات الملاك';
    protected static ?string $modelLabel = 'تسوية مالك';
    protected static ?string $pluralModelLabel = 'تسويات الملاك';
    protected static string|UnitEnum|null $navigationGroup = 'الملاك والتسويات';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_management_agreement_id')
                ->label('اتفاق الإدارة')
                ->relationship('agreement', 'agreement_number')
                ->getOptionLabelFromRecordUsing(fn ($record): string =>
                    $record->agreement_number.' — '.($record->property?->internal_code ?? 'عقار'))
                ->searchable()
                ->preload()
                ->required(),

            DatePicker::make('period_start')
                ->label('بداية فترة التسوية')
                ->default(now()->startOfMonth())
                ->required(),

            DatePicker::make('period_end')
                ->label('نهاية فترة التسوية')
                ->default(today())
                ->afterOrEqual('period_start')
                ->required(),

            Select::make('currency_id')
                ->label('العملة')
                ->relationship('currency', 'name_ar')
                ->searchable()
                ->preload()
                ->required(),

            Textarea::make('notes')
                ->label('ملاحظات')
                ->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('period_end', 'desc')
            ->columns([
                TextColumn::make('settlement_number')->label('رقم التسوية')->searchable()->copyable(),
                TextColumn::make('property.internal_code')->label('العقار')->searchable(),
                TextColumn::make('owner_name')
                    ->label('المالك')
                    ->state(fn (OwnerSettlement $record): string =>
                        $record->owner?->external_owner_name ?: ($record->owner?->user?->name ?? '—')),
                TextColumn::make('period_start')->label('من')->date('Y-m-d'),
                TextColumn::make('period_end')->label('إلى')->date('Y-m-d'),
                TextColumn::make('gross_collections')->label('التحصيلات')->numeric(),
                TextColumn::make('owner_expenses')->label('مصروفات المالك')->numeric(),
                TextColumn::make('management_fee')->label('رسوم وصال')->numeric(),
                TextColumn::make('net_payable')->label('صافي المستحق')->numeric(),
                TextColumn::make('currency.code')->label('العملة'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        OwnerSettlement::STATUS_APPROVED => 'معتمدة',
                        OwnerSettlement::STATUS_PAID => 'مدفوعة',
                        OwnerSettlement::STATUS_CANCELLED => 'ملغاة',
                        default => 'مسودة',
                    })
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        OwnerSettlement::STATUS_DRAFT => 'مسودة',
                        OwnerSettlement::STATUS_APPROVED => 'معتمدة',
                        OwnerSettlement::STATUS_PAID => 'مدفوعة',
                        OwnerSettlement::STATUS_CANCELLED => 'ملغاة',
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
                Action::make('approve')
                    ->label('اعتماد')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (OwnerSettlement $record): bool =>
                        $record->status === OwnerSettlement::STATUS_DRAFT
                        && (auth()->user()?->can('owner_settlements.manage') ?? false))
                    ->action(fn (OwnerSettlement $record) =>
                        app(OwnerSettlementService::class)->approve($record, auth()->user())),

                Action::make('mark_paid')
                    ->label('تسجيل السداد')
                    ->icon('heroicon-o-banknotes')
                    ->color('primary')
                    ->form([
                        TextInput::make('payment_reference')
                            ->label('مرجع السداد')
                            ->maxLength(255),
                    ])
                    ->visible(fn (OwnerSettlement $record): bool =>
                        $record->status === OwnerSettlement::STATUS_APPROVED
                        && (auth()->user()?->can('owner_settlements.manage') ?? false))
                    ->action(fn (OwnerSettlement $record, array $data) =>
                        app(OwnerSettlementService::class)->markPaid(
                            $record,
                            auth()->user(),
                            $data['payment_reference'] ?? null,
                        )),
            ]);
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOwnerSettlements::route('/'),
            'create' => CreateOwnerSettlement::route('/create'),
        ];
    }
}
