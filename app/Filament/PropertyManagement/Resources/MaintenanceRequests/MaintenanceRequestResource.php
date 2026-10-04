<?php

namespace App\Filament\PropertyManagement\Resources\MaintenanceRequests;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\PropertyManagement\Resources\MaintenanceRequests\Pages\CreateMaintenanceRequest;
use App\Filament\PropertyManagement\Resources\MaintenanceRequests\Pages\EditMaintenanceRequest;
use App\Filament\PropertyManagement\Resources\MaintenanceRequests\Pages\ListMaintenanceRequests;
use App\Models\MaintenanceRequest;
use App\Services\MaintenanceWorkflowService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
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

class MaintenanceRequestResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = MaintenanceRequest::class;
    protected static string $viewPermission = 'maintenance_requests.view';
    protected static string $managePermission = 'maintenance_requests.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static ?string $navigationLabel = 'الصيانة';
    protected static ?string $modelLabel = 'طلب صيانة';
    protected static ?string $pluralModelLabel = 'طلبات الصيانة';
    protected static string|UnitEnum|null $navigationGroup = 'الخدمات والصيانة';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('reference_number')
                ->label('رقم الطلب')
                ->disabled()
                ->dehydrated(false),

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
                ->getOptionLabelFromRecordUsing(fn ($record): string => $record->code.' — '.$record->name)
                ->searchable(['code', 'name'])
                ->preload(),

            Select::make('tenant_id')
                ->label('المستأجر المرتبط')
                ->relationship('tenant', 'name')
                ->searchable()
                ->preload(),

            Select::make('property_service_id')
                ->label('نوع الخدمة')
                ->relationship('service', 'name_ar')
                ->searchable()
                ->preload(),

            TextInput::make('title')
                ->label('عنوان الطلب')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->label('وصف المشكلة / العمل')
                ->rows(4),

            Select::make('priority')
                ->label('الأولوية')
                ->options([
                    MaintenanceRequest::PRIORITY_LOW => 'منخفضة',
                    MaintenanceRequest::PRIORITY_NORMAL => 'عادية',
                    MaintenanceRequest::PRIORITY_HIGH => 'عالية',
                    MaintenanceRequest::PRIORITY_URGENT => 'عاجلة',
                ])
                ->default(MaintenanceRequest::PRIORITY_NORMAL)
                ->required(),

            Select::make('status')
                ->label('الحالة')
                ->options([
                    MaintenanceRequest::STATUS_OPEN => 'مفتوح',
                    MaintenanceRequest::STATUS_SCHEDULED => 'مجدول',
                    MaintenanceRequest::STATUS_IN_PROGRESS => 'قيد التنفيذ',
                    MaintenanceRequest::STATUS_ON_HOLD => 'معلق',
                    MaintenanceRequest::STATUS_COMPLETED => 'مكتمل',
                    MaintenanceRequest::STATUS_CANCELLED => 'ملغي',
                ])
                ->default(MaintenanceRequest::STATUS_OPEN)
                ->required(),

            DateTimePicker::make('scheduled_at')
                ->label('موعد التنفيذ')
                ->seconds(false),

            Select::make('assigned_to_user_id')
                ->label('المسؤول الداخلي')
                ->relationship('assignee', 'name')
                ->searchable()
                ->preload(),

            Select::make('property_vendor_id')
                ->label('المورد / الفني')
                ->relationship('vendor', 'name')
                ->searchable()
                ->preload(),

            TextInput::make('estimated_cost')
                ->label('التكلفة التقديرية')
                ->numeric()
                ->minValue(0),

            TextInput::make('actual_cost')
                ->label('التكلفة الفعلية')
                ->numeric()
                ->minValue(0),

            Select::make('currency_id')
                ->label('العملة')
                ->relationship('currency', 'name_ar')
                ->searchable()
                ->preload(),

            Select::make('cost_bearer')
                ->label('من يتحمل التكلفة')
                ->options([
                    MaintenanceRequest::COST_OWNER => 'المالك',
                    MaintenanceRequest::COST_TENANT => 'المستأجر',
                    MaintenanceRequest::COST_WASAL => 'وصال',
                    MaintenanceRequest::COST_SHARED => 'مشتركة',
                ])
                ->default(MaintenanceRequest::COST_OWNER)
                ->required(),

            Toggle::make('is_paid')
                ->label('تم سداد التكلفة')
                ->default(false),

            FileUpload::make('attachments')
                ->label('صور ومرفقات')
                ->disk('public')
                ->directory('property-management/maintenance')
                ->multiple()
                ->downloadable()
                ->openable(),

            Textarea::make('notes')
                ->label('ملاحظات')
                ->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference_number')
                    ->label('رقم الطلب')
                    ->searchable(),

                TextColumn::make('title')
                    ->label('الطلب')
                    ->searchable()
                    ->limit(30)
                    ->wrap(),

                TextColumn::make('property.internal_code')
                    ->label('العقار')
                    ->searchable(),

                TextColumn::make('unit.code')
                    ->label('الوحدة')
                    ->placeholder('—'),

                TextColumn::make('service.name_ar')
                    ->label('الخدمة')
                    ->placeholder('غير مصنفة'),

                TextColumn::make('priority')
                    ->label('الأولوية')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        MaintenanceRequest::PRIORITY_LOW => 'منخفضة',
                        MaintenanceRequest::PRIORITY_HIGH => 'عالية',
                        MaintenanceRequest::PRIORITY_URGENT => 'عاجلة',
                        default => 'عادية',
                    })
                    ->badge(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        MaintenanceRequest::STATUS_SCHEDULED => 'مجدول',
                        MaintenanceRequest::STATUS_IN_PROGRESS => 'قيد التنفيذ',
                        MaintenanceRequest::STATUS_ON_HOLD => 'معلق',
                        MaintenanceRequest::STATUS_COMPLETED => 'مكتمل',
                        MaintenanceRequest::STATUS_CANCELLED => 'ملغي',
                        default => 'مفتوح',
                    })
                    ->badge()
                    ->sortable(),

                TextColumn::make('scheduled_at')
                    ->label('الموعد')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('vendor.name')
                    ->label('الفني / المورد')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('actual_cost')
                    ->label('التكلفة')
                    ->numeric()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_paid')
                    ->label('مسدد')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        MaintenanceRequest::STATUS_OPEN => 'مفتوح',
                        MaintenanceRequest::STATUS_SCHEDULED => 'مجدول',
                        MaintenanceRequest::STATUS_IN_PROGRESS => 'قيد التنفيذ',
                        MaintenanceRequest::STATUS_ON_HOLD => 'معلق',
                        MaintenanceRequest::STATUS_COMPLETED => 'مكتمل',
                        MaintenanceRequest::STATUS_CANCELLED => 'ملغي',
                    ]),

                SelectFilter::make('priority')
                    ->label('الأولوية')
                    ->options([
                        MaintenanceRequest::PRIORITY_LOW => 'منخفضة',
                        MaintenanceRequest::PRIORITY_NORMAL => 'عادية',
                        MaintenanceRequest::PRIORITY_HIGH => 'عالية',
                        MaintenanceRequest::PRIORITY_URGENT => 'عاجلة',
                    ]),
            ])
            ->striped()
            ->defaultPaginationPageOption(10)
            ->recordActions([
                Action::make('start')
                    ->label('بدء التنفيذ')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->visible(fn (MaintenanceRequest $record): bool =>
                        in_array($record->status, [
                            MaintenanceRequest::STATUS_OPEN,
                            MaintenanceRequest::STATUS_SCHEDULED,
                            MaintenanceRequest::STATUS_ON_HOLD,
                        ], true)
                        && (auth()->user()?->can('maintenance_requests.manage') ?? false))
                    ->action(function (MaintenanceRequest $record): void {
                        $record->update([
                            'status' => MaintenanceRequest::STATUS_IN_PROGRESS,
                            'started_at' => $record->started_at ?? now(),
                        ]);

                        app(MaintenanceWorkflowService::class)->syncTask($record->fresh());
                    }),

                Action::make('complete')
                    ->label('إكمال')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (MaintenanceRequest $record): bool =>
                        ! in_array($record->status, [
                            MaintenanceRequest::STATUS_COMPLETED,
                            MaintenanceRequest::STATUS_CANCELLED,
                        ], true)
                        && (auth()->user()?->can('maintenance_requests.manage') ?? false))
                    ->action(function (MaintenanceRequest $record): void {
                        $record->update([
                            'status' => MaintenanceRequest::STATUS_COMPLETED,
                            'completed_at' => now(),
                        ]);

                        app(MaintenanceWorkflowService::class)->syncTask($record->fresh());
                    }),

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
            'index' => ListMaintenanceRequests::route('/'),
            'create' => CreateMaintenanceRequest::route('/create'),
            'edit' => EditMaintenanceRequest::route('/{record}/edit'),
        ];
    }
}
