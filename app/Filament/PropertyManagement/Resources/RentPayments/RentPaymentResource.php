<?php

namespace App\Filament\PropertyManagement\Resources\RentPayments;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\PropertyManagement\Resources\RentPayments\Pages\CreateRentPayment;
use App\Filament\PropertyManagement\Resources\RentPayments\Pages\ListRentPayments;
use App\Models\RentPayment;
use App\Services\RentPaymentService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class RentPaymentResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = RentPayment::class;
    protected static string $viewPermission = 'rent_payments.view';
    protected static string $managePermission = 'rent_payments.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'التحصيلات والدفعات';
    protected static ?string $modelLabel = 'دفعة إيجار';
    protected static ?string $pluralModelLabel = 'التحصيلات والدفعات';
    protected static string|UnitEnum|null $navigationGroup = 'الإيجارات والتحصيل';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('rent_due_item_id')
                ->label('الاستحقاق')
                ->relationship('dueItem', 'id')
                ->getOptionLabelFromRecordUsing(function ($record): string {
                    $tenant = $record->tenancy?->tenant?->name ?? 'مستأجر';
                    $property = $record->tenancy?->unit?->property?->internal_code ?? 'عقار';
                    $remaining = max(0, (float) $record->amount - (float) $record->paid_amount);

                    return '#'.$record->id.' — '.$tenant.' — '.$property.' — المتبقي: '.number_format($remaining, 2);
                })
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('amount')
                ->label('مبلغ التحصيل')
                ->numeric()
                ->minValue(0.01)
                ->required(),

            Select::make('currency_id')
                ->label('العملة')
                ->relationship('currency', 'name_ar')
                ->searchable()
                ->preload()
                ->required(),

            DateTimePicker::make('paid_at')
                ->label('تاريخ ووقت الدفع')
                ->seconds(false)
                ->default(now())
                ->required(),

            Select::make('payment_method')
                ->label('طريقة الدفع')
                ->options([
                    'cash' => 'نقدي',
                    'bank_transfer' => 'تحويل بنكي',
                    'mobile_wallet' => 'محفظة إلكترونية',
                    'cheque' => 'شيك',
                    'other' => 'أخرى',
                ])
                ->default('cash')
                ->required(),

            Select::make('bank_id')
                ->label('الحساب البنكي')
                ->relationship('bank', 'name')
                ->searchable()
                ->preload(),

            TextInput::make('reference_number')
                ->label('رقم المرجع / الحوالة')
                ->maxLength(255),

            FileUpload::make('attachment_path')
                ->label('سند / صورة التحويل')
                ->disk('public')
                ->directory('property-management/rent-payments')
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
            ->defaultSort('paid_at', 'desc')
            ->columns([
                TextColumn::make('receipt_number')
                    ->label('رقم السند')
                    ->searchable(),

                TextColumn::make('dueItem.tenancy.tenant.name')
                    ->label('المستأجر')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('dueItem.tenancy.unit.property.internal_code')
                    ->label('العقار')
                    ->placeholder('—'),

                TextColumn::make('dueItem.tenancy.unit.code')
                    ->label('الوحدة')
                    ->placeholder('—'),

                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('currency.code')
                    ->label('العملة'),

                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bank_transfer' => 'تحويل بنكي',
                        'mobile_wallet' => 'محفظة إلكترونية',
                        'cheque' => 'شيك',
                        'other' => 'أخرى',
                        default => 'نقدي',
                    })
                    ->badge(),

                TextColumn::make('paid_at')
                    ->label('تاريخ الدفع')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string =>
                        $state === RentPayment::STATUS_VOIDED ? 'ملغاة' : 'مرحّلة')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        RentPayment::STATUS_POSTED => 'مرحّلة',
                        RentPayment::STATUS_VOIDED => 'ملغاة',
                    ]),
            ])
            ->striped()
            ->defaultPaginationPageOption(10)
            ->recordActions([
                Action::make('void')
                    ->label('إلغاء الدفعة')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('reason')
                            ->label('سبب الإلغاء')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn (RentPayment $record): bool =>
                        $record->status === RentPayment::STATUS_POSTED
                        && (auth()->user()?->can('rent_payments.manage') ?? false))
                    ->action(function (RentPayment $record, array $data): void {
                        app(RentPaymentService::class)->void(
                            payment: $record,
                            actor: auth()->user(),
                            reason: $data['reason'],
                        );

                        Notification::make()
                            ->success()
                            ->title('تم إلغاء الدفعة وإعادة احتساب الاستحقاق')
                            ->send();
                    }),
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

    public static function canEdit(Model $record): bool
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
            'index' => ListRentPayments::route('/'),
            'create' => CreateRentPayment::route('/create'),
        ];
    }
}
