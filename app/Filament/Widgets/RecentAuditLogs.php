<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Models\AuditLog;
use App\Models\Bank;
use App\Models\Currency;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentAuditLogs extends TableWidget
{
    protected static ?string $heading = 'آخر العمليات';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can('audit_logs.view')
            || $user->can('audit_logs.financial_view');
    }

    protected function getAuditQuery(): Builder
    {
        $query = AuditLog::query()
            ->with('actor')
            ->latest('id');

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        /*
        |--------------------------------------------------------------------------
        | Full Audit Access
        |--------------------------------------------------------------------------
        */

        if ($user->can('audit_logs.view')) {
            return $query;
        }

        /*
        |--------------------------------------------------------------------------
        | Financial Audit Access
        |--------------------------------------------------------------------------
        */

        if ($user->can('audit_logs.financial_view')) {
            return $query->whereIn(
                'entity_type',
                [
                    Bank::class,
                    Currency::class,
                ]
            );
        }

        return $query->whereRaw('1 = 0');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                $this->getAuditQuery()
            )
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('لا توجد عمليات مسجلة حتى الآن')
            ->emptyStateDescription(
                'ستظهر هنا العمليات والتعديلات المسموح لك بمشاهدتها.'
            )
            ->columns([
                TextColumn::make('actor.name')
                    ->label('المستخدم')
                    ->placeholder('النظام'),

                TextColumn::make('action')
                    ->label('العملية')
                    ->searchable(),

                TextColumn::make('entity_type')
                    ->label('نوع السجل')
                    ->formatStateUsing(
                        fn (?string $state): string =>
                            $state
                                ? class_basename($state)
                                : '—'
                    ),

                TextColumn::make('entity_id')
                    ->label('رقم السجل')
                    ->placeholder('—'),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('التفاصيل')
                    ->url(
                        fn (AuditLog $record): string =>
                            AuditLogResource::getUrl(
                                'view',
                                ['record' => $record]
                            )
                    ),
            ]);
    }
}