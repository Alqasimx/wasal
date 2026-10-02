<?php
namespace App\Filament\Resources\PropertyOwners\Tables;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
class PropertyOwnersTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('property_image')
                ->label('صورة العقار')
                ->state(fn ($record): ?string => $record->property?->gallery[0] ?? null)
                ->disk('public')
                ->height(104)
                ->width(148),
            TextColumn::make('property.internal_code')->label('العقار')->searchable(),
            TextColumn::make('user.name')->label('المستخدم'),
            TextColumn::make('external_owner_name')->label('المالك الخارجي'),
            TextColumn::make('ownership_percentage')->label('النسبة'),
            IconColumn::make('is_primary')->label('أساسي')->boolean(),
        ])->striped()->defaultPaginationPageOption(10)->recordActions([EditAction::make()]);
    }
}
