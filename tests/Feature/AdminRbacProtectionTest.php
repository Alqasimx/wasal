<?php

namespace Tests\Feature;

use App\Filament\Resources\Permissions\PermissionResource;
use App\Filament\Resources\Roles\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRbacProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_system_admin_has_all_permissions(): void
    {
        $role = Role::query()
            ->where('name', 'system_admin')
            ->firstOrFail();

        $allPermissions = Permission::query()
            ->pluck('name')
            ->sort()
            ->values();

        $rolePermissions = $role
            ->permissions()
            ->pluck('name')
            ->sort()
            ->values();

        $this->assertSame(
            $allPermissions->all(),
            $rolePermissions->all()
        );
    }

    public function test_system_admin_role_cannot_be_deleted_through_filament_resource(): void
    {
        $role = Role::query()
            ->where('name', 'system_admin')
            ->firstOrFail();

        $this->assertFalse(
            RoleResource::canDelete($role)
        );
    }

    public function test_permissions_cannot_be_deleted_through_filament_resource(): void
    {
        $permission = Permission::query()->firstOrFail();

        $this->assertFalse(
            PermissionResource::canDelete($permission)
        );

        $this->assertFalse(
            PermissionResource::canDeleteAny()
        );
    }
}