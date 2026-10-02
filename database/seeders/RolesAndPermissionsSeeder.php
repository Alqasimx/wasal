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
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [
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

            'tasks.view',
            'tasks.manage',

            'banks.view',
            'banks.manage',

            'settings.view',
            'settings.manage',

            // مشاهدة سجل التدقيق الكامل
            'audit_logs.view',

            // مشاهدة السجلات المالية فقط
            'audit_logs.financial_view',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

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
                'users.view',
                'geography.view',
                'currencies.view',
                'tasks.view',
                'tasks.manage',
            ]);

        Role::findByName('property_reviewer', 'web')
            ->syncPermissions([
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
                'tasks.view',
                'tasks.manage',
            ]);

        Role::findByName('property_management', 'web')
            ->syncPermissions([
                'users.view',
                'geography.view',
                'currencies.view',
                'property_types.view',
                'property_features.view',
                'properties.view',
                'property_listings.view',
                'tasks.view',
                'tasks.manage',
            ]);

        Role::findByName('accountant', 'web')
            ->syncPermissions([
                'banks.view',
                'banks.manage',
                'currencies.view',
                'audit_logs.financial_view',
            ]);

        Role::findByName('financial_approver', 'web')
            ->syncPermissions([
                'banks.view',
                'banks.manage',
                'currencies.view',
                'currencies.manage',
                'settings.view',
                'audit_logs.financial_view',
            ]);

        $systemAdmin = Role::findByName('system_admin', 'web');

        $systemAdmin->syncPermissions(
            Permission::query()
                ->where('guard_name', 'web')
                ->get()
        );

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
