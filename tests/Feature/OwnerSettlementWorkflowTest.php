<?php

namespace Tests\Feature;

use App\Models\OwnerSettlement;
use App\Models\PropertyManagementAgreement;
use App\Models\User;
use App\Services\OwnerSettlementService;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerSettlementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_settlement_can_be_generated_approved_and_paid(): void
    {
        $this->seed(DemoDataSeeder::class);

        $actor = User::query()
            ->where('email', 'demo.manager@wasal.local')
            ->firstOrFail();

        $agreement = PropertyManagementAgreement::query()
            ->where('agreement_number', 'DEMO-PMA-001')
            ->firstOrFail();

        $settlement = app(OwnerSettlementService::class)->generate(
            agreement: $agreement,
            periodStart: now()->subMonth()->startOfMonth(),
            periodEnd: today(),
            currencyId: $agreement->currency_id,
            actor: $actor,
            notes: 'اختبار التسوية',
        );

        $this->assertSame(OwnerSettlement::STATUS_DRAFT, $settlement->status);

        app(OwnerSettlementService::class)->approve($settlement, $actor);
        $this->assertSame(OwnerSettlement::STATUS_APPROVED, $settlement->fresh()->status);

        app(OwnerSettlementService::class)->markPaid(
            $settlement->fresh(),
            $actor,
            'TEST-PAYMENT',
        );

        $this->assertSame(OwnerSettlement::STATUS_PAID, $settlement->fresh()->status);
    }
}
