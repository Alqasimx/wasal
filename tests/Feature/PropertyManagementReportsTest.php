<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Services\PropertyManagementReportService;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyManagementReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_dashboard_returns_operational_and_currency_safe_kpis(): void
    {
        $this->seed(DemoDataSeeder::class);

        $report = app(PropertyManagementReportService::class)->dashboard();

        $this->assertSame(3, $report['kpis']['managed_properties']);
        $this->assertGreaterThanOrEqual(3, $report['kpis']['total_units']);
        $this->assertArrayHasKey('collections', $report);
        $this->assertArrayHasKey('expenses', $report);
        $this->assertArrayHasKey('outstanding', $report);

        foreach (['collections', 'expenses', 'outstanding'] as $bucket) {
            foreach ($report[$bucket] as $row) {
                $this->assertArrayHasKey('currency', $row);
                $this->assertArrayHasKey('total', $row);
            }
        }
    }

    public function test_property_manager_can_export_collections_csv(): void
    {
        $this->seed(DemoDataSeeder::class);

        $user = User::query()
            ->where('email', 'demo.manager@wasal.local')
            ->firstOrFail();

        $this->actingAs($user)
            ->get('/property-management/report-exports/collections?from='
                .now()->startOfMonth()->toDateString()
                .'&to='.today()->toDateString())
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_advanced_alert_sync_is_idempotent_for_expiring_documents(): void
    {
        $this->seed(DemoDataSeeder::class);

        $this->artisan('wasal:sync-property-management-alerts')
            ->assertSuccessful();

        $first = Task::query()
            ->where('related_type', 'property_document')
            ->count();

        $this->artisan('wasal:sync-property-management-alerts')
            ->assertSuccessful();

        $second = Task::query()
            ->where('related_type', 'property_document')
            ->count();

        $this->assertGreaterThan(0, $first);
        $this->assertSame($first, $second);
    }
}
