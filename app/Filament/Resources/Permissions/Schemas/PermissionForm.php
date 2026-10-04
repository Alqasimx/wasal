<?php

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PermissionForm
{
    private const CORE_PERMISSIONS = [
        'panels.admin.access',
        'panels.real_estate.access',
        'panels.property_management.access',
        'users.view',
        'users.manage',
        'roles.view',
        'roles.manage',
        'permissions.view',
        'permissions.manage',
        'geography.view',
        'geography.manage',
        'currencies.view',
        'currencies.manage',
        'property_types.view',
        'property_types.manage',
        'property_features.view',
        'property_features.manage',
        'properties.view',
        'properties.manage',
        'property_listings.view',
        'property_listings.manage',
        'property_units.view',
        'property_units.manage',
        'property_owners.view',
        'property_owners.manage',
        'property_requests.view',
        'property_requests.manage',
        'tenants.view',
        'tenants.manage',
        'tenancies.view',
        'tenancies.manage',
        'rent_due_items.view',
        'rent_due_items.manage',
        'rent_payments.view',
        'rent_payments.manage',
        'property_management_agreements.view',
        'property_management_agreements.manage',
        'property_services.view',
        'property_services.manage',
        'property_service_schedules.view',
        'property_service_schedules.manage',
        'property_vendors.view',
        'property_vendors.manage',
        'maintenance_requests.view',
        'maintenance_requests.manage',
        'property_expenses.view',
        'property_expenses.manage',
        'owner_settlements.view',
        'owner_settlements.manage',
        'property_inspections.view',
        'property_inspections.manage',
        'utility_meters.view',
        'utility_meters.manage',
        'utility_meter_readings.view',
        'utility_meter_readings.manage',
        'property_documents.view',
        'property_documents.manage',
        'tasks.view',
        'tasks.manage',
        'banks.view',
        'banks.manage',
        'settings.view',
        'settings.manage',
        'audit_logs.view',
        'audit_logs.financial_view',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم الصلاحية')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->disabled(
                        fn ($record): bool =>
                            $record !== null &&
                            in_array($record->name, self::CORE_PERMISSIONS, true)
                    ),

                TextInput::make('guard_name')
                    ->label('Guard')
                    ->default('web')
                    ->required()
                    ->maxLength(255)
                    ->disabled(
                        fn ($record): bool =>
                            $record !== null &&
                            in_array($record->name, self::CORE_PERMISSIONS, true)
                    ),
            ]);
    }
}
