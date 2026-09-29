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

            'banks.view',
            'banks.manage',

            'settings.view',
            'settings.manage',

            'audit_logs.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
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

        $systemAdmin = Role::findByName('system_admin', 'web');

        $systemAdmin->syncPermissions(
            Permission::where('guard_name', 'web')->get()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}