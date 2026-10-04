<?php

namespace App\Filament\PropertyManagement\Resources\PropertyInspections;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\PropertyManagement\Resources\PropertyInspections\Pages\CreatePropertyInspection;
use App\Filament\PropertyManagement\Resources\PropertyInspections\Pages\EditPropertyInspection;
use App\Filament\PropertyManagement\Resources\PropertyInspections\Pages\ListPropertyInspections;
use App\Models\PropertyInspection;
use App\Services\AuditService;
use App\Services\InspectionWorkflowService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
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

class PropertyInspectionResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = PropertyInspection::class;
    protected static string $viewPermission = 'property_inspections.view';
    protected static string $managePermission = 'property_inspections.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;
    protected static ?string $navigationLabel = 'المعاينات والاستلام';
    protected static ?string $modelLabel = 'معاينة';
    protected static ?string $pluralModelLabel = 'المعاينات والاستلام';
    protected static string|UnitEnum|null $navigationGroup = 'العمليات المتقدمة';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_id')
                ->label('العقار')
                ->relationship('property', 'internal_code')
                ->getOptionLabelFromRecordUsing(fn ($record): string =>
                    $record->internal_code.' — '.$record->title_ar)
                ->searchable(['internal_code', 'title_ar'])
                ->preload()
                ->required(),

            Select::make('property_unit_id')
                ->label('الوحدة')
                ->relationship('unit', 'code')
                ->searchable()
                ->preload(),

            Select::make('tenancy_id')
                ->label('عقد الإيجار')
                ->relationship('tenancy', 'contract_number')
                ->searchable()
                ->preload(),

            Select::make('inspection_type')
                ->label('نوع المعاينة')
                ->options([
                    PropertyInspection::TYPE_PERIODIC => 'معاينة دورية',
                    PropertyInspection::TYPE_CHECK_IN => 'استلام عند بداية العقد',
                    PropertyInspection::TYPE_CHECK_OUT => 'تسليم عند نهاية العقد',
                    PropertyInspection::TYPE_CONDITION => 'تقييم حالة العقار',
                ])
                ->required(),

            Select::make('status')
                ->label('الحالة')
                ->options([
                    PropertyInspection::STATUS_SCHEDULED => 'مجدولة',
                    PropertyInspection::STATUS_IN_PROGRESS => 'قيد التنفيذ',
                    PropertyInspection::STATUS_COMPLETED => 'مكتملة',
                    PropertyInspection::STATUS_CANCELLED => 'ملغاة',
                ])
                ->default(PropertyInspection::STATUS_SCHEDULED)
                ->required(),

            DateTimePicker::make('scheduled_at')
                ->label('موعد المعاينة')
                ->seconds(false)
                ->required(),

            Select::make('inspector_user_id')
                ->label('المعاين / المسؤول')
                ->relationship('inspector', 'name')
                ->searchable()
                ->preload(),

            TextInput::make('condition_score')
                ->label('تقييم الحالة من 1 إلى 5')
                ->numeric()
                ->minValue(1)
                ->maxValue(5),

            Textarea::make('notes')
                ->label('ملاحظات المعاينة')
                ->rows(4),

            FileUpload::make('attachments')
                ->label('صور ومرفقات المعاينة')
                ->multiple()
                ->disk('public')
                ->directory('property-management/inspections')
                ->downloadable()
                ->openable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('scheduled_at', 'desc')
            ->columns([
                TextColumn::make('inspection_number')->label('رقم المعاينة')->searchable()->copyable(),
                TextColumn::make('property.internal_code')->label('العقار')->searchable(),
                TextColumn::make('unit.code')->label('الوحدة')->placeholder('كامل العقار'),
                TextColumn::make('inspection_type')
                    ->label('النوع')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        PropertyInspection::TYPE_CHECK_IN => 'استلام',
                        PropertyInspection::TYPE_CHECK_OUT => 'تسليم',
                        PropertyInspection::TYPE_CONDITION => 'تقييم حالة',
                        default => 'دورية',
                    })
                    ->badge(),
                TextColumn::make('scheduled_at')->label('الموعد')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('inspector.name')->label('المسؤول')->placeholder('—'),
                TextColumn::make('condition_score')->label('التقييم')->placeholder('—'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        PropertyInspection::STATUS_IN_PROGRESS => 'قيد التنفيذ',
                        PropertyInspection::STATUS_COMPLETED => 'مكتملة',
                        PropertyInspection::STATUS_CANCELLED => 'ملغاة',
                        default => 'مجدولة',
                    })
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('inspection_type')
                    ->label('نوع المعاينة')
                    ->options([
                        PropertyInspection::TYPE_PERIODIC => 'دورية',
                        PropertyInspection::TYPE_CHECK_IN => 'استلام',
                        PropertyInspection::TYPE_CHECK_OUT => 'تسليم',
                        PropertyInspection::TYPE_CONDITION => 'تقييم حالة',
                    ]),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        PropertyInspection::STATUS_SCHEDULED => 'مجدولة',
                        PropertyInspection::STATUS_IN_PROGRESS => 'قيد التنفيذ',
                        PropertyInspection::STATUS_COMPLETED => 'مكتملة',
                        PropertyInspection::STATUS_CANCELLED => 'ملغاة',
                    ]),
            ])
            ->striped()
            ->recordActions([
                Action::make('start')
                    ->label('بدء المعاينة')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->visible(fn (PropertyInspection $record): bool =>
                        $record->status === PropertyInspection::STATUS_SCHEDULED
                        && (auth()->user()?->can('property_inspections.manage') ?? false))
                    ->action(function (PropertyInspection $record): void {
                        $old = $record->toArray();
                        $record->update([
                            'status' => PropertyInspection::STATUS_IN_PROGRESS,
                            'started_at' => now(),
                        ]);
                        $record = $record->fresh();
                        app(InspectionWorkflowService::class)->syncTask($record);
                        app(AuditService::class)->forModel(
                            action: 'inspection.started',
                            model: $record,
                            oldValues: $old,
                            newValues: $record->toArray(),
                            actor: auth()->user(),
                            request: request(),
                        );
                    }),
                Action::make('complete')
                    ->label('إكمال المعاينة')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (PropertyInspection $record): bool =>
                        $record->status === PropertyInspection::STATUS_IN_PROGRESS
                        && (auth()->user()?->can('property_inspections.manage') ?? false))
                    ->action(function (PropertyInspection $record): void {
                        $old = $record->toArray();
                        $record->update([
                            'status' => PropertyInspection::STATUS_COMPLETED,
                            'completed_at' => now(),
                        ]);
                        $record = $record->fresh();
                        app(InspectionWorkflowService::class)->syncTask($record);
                        app(AuditService::class)->forModel(
                            action: 'inspection.completed',
                            model: $record,
                            oldValues: $old,
                            newValues: $record->toArray(),
                            actor: auth()->user(),
                            request: request(),
                        );
                    }),
                EditAction::make()->label('تعديل'),
            ]);
    }

    public static function canDelete(Model $record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    public static function getPages(): array
    {
        return [
            'index' => ListPropertyInspections::route('/'),
            'create' => CreatePropertyInspection::route('/create'),
            'edit' => EditPropertyInspection::route('/{record}/edit'),
        ];
    }
}
