<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
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

            'tasks.view',
            'tasks.manage',

            'banks.view',
            'banks.manage',

            'settings.view',
            'settings.manage',

            'audit_logs.view',
            'audit_logs.financial_view',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $roles = [
            'user',
            'customer_service',
            'property_reviewer',
            'property_management',
            'accountant',
            'financial_approver',
            'system_admin',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        Role::findByName('user', 'web')
            ->syncPermissions([]);

        Role::findByName('customer_service', 'web')
            ->syncPermissions([
                'panels.admin.access',
                'panels.real_estate.access',
                'users.view',
                'geography.view',
                'currencies.view',
                'tasks.view',
                'tasks.manage',
            ]);

        Role::findByName('property_reviewer', 'web')
            ->syncPermissions([
                'panels.real_estate.access',
                'geography.view',
                'currencies.view',
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
                'tasks.view',
                'tasks.manage',
            ]);

        Role::findByName('property_management', 'web')
            ->syncPermissions([
                'panels.property_management.access',
                'users.view',
                'geography.view',
                'currencies.view',
                'property_types.view',
                'property_features.view',
                'properties.view',
                'property_units.view',
                'property_units.manage',
                'property_owners.view',
                'property_owners.manage',
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
                'tasks.view',
                'tasks.manage',
            ]);

        Role::findByName('accountant', 'web')
            ->syncPermissions([
                'panels.admin.access',
                'panels.property_management.access',
                'banks.view',
                'banks.manage',
                'currencies.view',
                'tenants.view',
                'tenancies.view',
                'rent_due_items.view',
                'rent_due_items.manage',
                'rent_payments.view',
                'rent_payments.manage',
                'property_expenses.view',
                'property_expenses.manage',
                'owner_settlements.view',
                'owner_settlements.manage',
                'audit_logs.financial_view',
            ]);

        Role::findByName('financial_approver', 'web')
            ->syncPermissions([
                'panels.admin.access',
                'panels.property_management.access',
                'banks.view',
                'banks.manage',
                'currencies.view',
                'currencies.manage',
                'settings.view',
                'tenants.view',
                'tenancies.view',
                'rent_due_items.view',
                'rent_payments.view',
                'property_expenses.view',
                'owner_settlements.view',
                'owner_settlements.manage',
                'audit_logs.financial_view',
            ]);

        $systemAdmin = Role::findByName('system_admin', 'web');

        $systemAdmin->syncPermissions(
            Permission::query()
                ->where('guard_name', 'web')
                ->get()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
