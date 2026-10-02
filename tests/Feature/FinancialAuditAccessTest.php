<?php

namespace Tests\Feature;

use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Models\AuditLog;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialAuditAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_accountant_can_view_financial_audit_logs_only(): void
    {
        $accountant = User::factory()->create([
            'status' => 'active',
        ]);

        $accountant->assignRole('accountant');

        $currencyLog = AuditLog::create([
            'actor_user_id' => $accountant->id,
            'action' => 'currency.updated',
            'entity_type' => Currency::class,
            'entity_id' => 1,
            'created_at' => now(),
        ]);

        $bankLog = AuditLog::create([
            'actor_user_id' => $accountant->id,
            'action' => 'bank.updated',
            'entity_type' => Bank::class,
            'entity_id' => 1,
            'created_at' => now(),
        ]);

        $userLog = AuditLog::create([
            'actor_user_id' => $accountant->id,
            'action' => 'user.updated',
            'entity_type' => User::class,
            'entity_id' => $accountant->id,
            'created_at' => now(),
        ]);

        $this->actingAs($accountant);

        $this->assertTrue(
            AuditLogResource::canView($currencyLog)
        );

        $this->assertTrue(
            AuditLogResource::canView($bankLog)
        );

        $this->assertFalse(
            AuditLogResource::canView($userLog)
        );
    }

    public function test_accountant_audit_query_contains_only_financial_records(): void
    {
        $accountant = User::factory()->create([
            'status' => 'active',
        ]);

        $accountant->assignRole('accountant');

        AuditLog::create([
            'actor_user_id' => $accountant->id,
            'action' => 'currency.updated',
            'entity_type' => Currency::class,
            'entity_id' => 1,
            'created_at' => now(),
        ]);

        AuditLog::create([
            'actor_user_id' => $accountant->id,
            'action' => 'bank.updated',
            'entity_type' => Bank::class,
            'entity_id' => 1,
            'created_at' => now(),
        ]);

        AuditLog::create([
            'actor_user_id' => $accountant->id,
            'action' => 'user.updated',
            'entity_type' => User::class,
            'entity_id' => $accountant->id,
            'created_at' => now(),
        ]);

        $this->actingAs($accountant);

        $logs = AuditLogResource::getEloquentQuery()
            ->get();

        $this->assertCount(2, $logs);

        $this->assertTrue(
            $logs->every(
                fn (AuditLog $log): bool =>
                    in_array(
                        $log->entity_type,
                        [
                            Currency::class,
                            Bank::class,
                        ],
                        true
                    )
            )
        );
    }

    public function test_system_admin_can_view_all_audit_logs(): void
    {
        $admin = User::factory()->create([
            'status' => 'active',
        ]);

        $admin->assignRole('system_admin');

        $userLog = AuditLog::create([
            'actor_user_id' => $admin->id,
            'action' => 'user.updated',
            'entity_type' => User::class,
            'entity_id' => $admin->id,
            'created_at' => now(),
        ]);

        $this->actingAs($admin);

        $this->assertTrue(
            AuditLogResource::canView($userLog)
        );

        $this->assertTrue(
            AuditLogResource::getEloquentQuery()
                ->whereKey($userLog->id)
                ->exists()
        );
    }
}