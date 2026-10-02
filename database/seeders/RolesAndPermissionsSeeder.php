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

        /*
        |--------------------------------------------------------------------------
        | Normal User
        |--------------------------------------------------------------------------
        */

        Role::findByName('user', 'web')
            ->syncPermissions([]);

        /*
        |--------------------------------------------------------------------------
        | Customer Service
        |--------------------------------------------------------------------------
        */

        Role::findByName('customer_service', 'web')
            ->syncPermissions([
                'users.view',
                'geography.view',
                'currencies.view',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Property Reviewer
        |--------------------------------------------------------------------------
        */

        Role::findByName('property_reviewer', 'web')
            ->syncPermissions([
                'geography.view',
                'currencies.view',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Property Management
        |--------------------------------------------------------------------------
        */

        Role::findByName('property_management', 'web')
            ->syncPermissions([
                'users.view',
                'geography.view',
                'currencies.view',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Accountant
        |--------------------------------------------------------------------------
        |
        | المحاسب يستطيع إدارة الحسابات البنكية ومشاهدة العملات،
        | لكنه لا يرى سجل النظام الكامل.
        |
        */

        Role::findByName('accountant', 'web')
            ->syncPermissions([
                'banks.view',
                'banks.manage',
                'currencies.view',
                'audit_logs.financial_view',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Financial Approver
        |--------------------------------------------------------------------------
        |
        | المعتمد المالي لديه صلاحيات مالية أوسع،
        | لكنه كذلك لا يرى سجل النظام الكامل.
        |
        */

        Role::findByName('financial_approver', 'web')
            ->syncPermissions([
                'banks.view',
                'banks.manage',
                'currencies.view',
                'currencies.manage',
                'settings.view',
                'audit_logs.financial_view',
            ]);

        /*
        |--------------------------------------------------------------------------
        | System Administrator
        |--------------------------------------------------------------------------
        |
        | مدير النظام يحصل دائمًا على جميع الصلاحيات الموجودة.
        |
        */

        $systemAdmin = Role::findByName(
            'system_admin',
            'web'
        );

        $systemAdmin->syncPermissions(
            Permission::query()
                ->where('guard_name', 'web')
                ->get()
        );

        /*
        |--------------------------------------------------------------------------
        | Clear Permission Cache
        |--------------------------------------------------------------------------
        */

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}