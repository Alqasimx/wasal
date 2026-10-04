<?php

namespace Tests\Feature;

use App\Models\RentDueItem;
use App\Models\RentPayment;
use App\Models\Tenancy;
use App\Models\User;
use App\Services\RentDueScheduleService;
use App\Services\RentPaymentService;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentCollectionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_tenancy_generates_idempotent_due_schedule(): void
    {
        $this->seed(DemoDataSeeder::class);

        $tenancy = Tenancy::query()
            ->where('contract_number', 'DEMO-TEN-1')
            ->firstOrFail();

        $service = app(RentDueScheduleService::class);

        $firstCreated = $service->generateForTenancy($tenancy);
        $secondCreated = $service->generateForTenancy($tenancy->fresh());

        $this->assertGreaterThan(0, $firstCreated);
        $this->assertSame(0, $secondCreated);

        $duplicates = RentDueItem::query()
            ->where('tenancy_id', $tenancy->id)
            ->selectRaw('due_date, COUNT(*) as aggregate')
            ->groupBy('due_date')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $this->assertSame(0, $duplicates);
    }

    public function test_partial_payment_and_void_recalculate_due_item(): void
    {
        $this->seed(DemoDataSeeder::class);

        $actor = User::query()
            ->where('email', 'demo.manager@wasal.local')
            ->firstOrFail();

        $dueItem = RentDueItem::query()
            ->whereHas('tenancy', fn ($query) =>
                $query->where('contract_number', 'DEMO-TEN-1'))
            ->whereColumn('paid_amount', '<', 'amount')
            ->orderBy('due_date')
            ->firstOrFail();

        $before = (float) $dueItem->paid_amount;
        $amount = min(1000, (float) $dueItem->amount - $before);

        $payment = app(RentPaymentService::class)->record([
            'rent_due_item_id' => $dueItem->id,
            'amount' => $amount,
            'currency_id' => $dueItem->currency_id,
            'paid_at' => now(),
            'payment_method' => 'cash',
            'bank_id' => null,
            'reference_number' => 'TEST-PARTIAL-001',
            'attachment_path' => null,
            'notes' => 'اختبار دفعة جزئية',
        ], $actor);

        $this->assertSame(
            $before + $amount,
            (float) $dueItem->fresh()->paid_amount
        );

        app(RentPaymentService::class)->void(
            $payment,
            $actor,
            'اختبار إلغاء دفعة',
        );

        $this->assertSame($before, (float) $dueItem->fresh()->paid_amount);
        $this->assertSame(RentPayment::STATUS_VOIDED, $payment->fresh()->status);
    }

    public function test_ended_contract_cancels_unpaid_future_dues(): void
    {
        $this->seed(DemoDataSeeder::class);

        $tenancy = Tenancy::query()
            ->where('contract_number', 'DEMO-TEN-2')
            ->firstOrFail();

        $service = app(RentDueScheduleService::class);
        $service->generateForTenancy($tenancy);

        $tenancy->update(['status' => Tenancy::STATUS_ENDED]);
        $service->generateForTenancy($tenancy->fresh());

        $remainingFuture = RentDueItem::query()
            ->where('tenancy_id', $tenancy->id)
            ->whereDate('due_date', '>', today())
            ->where('paid_amount', '<=', 0)
            ->where('status', '!=', RentDueItem::STATUS_CANCELLED)
            ->count();

        $this->assertSame(0, $remainingFuture);
    }
}
