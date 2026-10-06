<?php

namespace App\Filament\PropertyManagement\Pages;

use App\Models\Property;
use App\Models\PropertyManagementAgreement;
use App\Models\PropertyDocument;
use App\Models\PropertyExpense;
use App\Models\PropertyServiceSchedule;
use App\Models\RentDueItem;
use App\Models\MaintenanceRequest;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class PropertyOverview extends Page
{
    protected string $view = 'filament.property-management.pages.property-overview';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?string $navigationLabel = 'ملف العقار';

    protected static ?string $title = 'ملف العقار';

    protected static ?int $navigationSort = 2;

    protected static string|\UnitEnum|null $navigationGroup = 'الأصول والإشغال';

    public Property $property;

    public Collection $activeAgreements;

    public Collection $upcomingServices;

    public Collection $openMaintenance;

    public Collection $recentExpenses;

    public Collection $expiringDocuments;

    public int $totalUnits = 0;

    public int $occupiedUnits = 0;

    public int $vacantUnits = 0;

    public int $activeTenancies = 0;

    public float $occupancyRate = 0.0;

    public float $outstandingRent = 0.0;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(Property $record): void
    {
        abort_unless(auth()->user()?->can('properties.view'), 403);

        $managed = $record->managementAgreements()
            ->whereIn('status', [
                PropertyManagementAgreement::STATUS_ACTIVE,
                PropertyManagementAgreement::STATUS_PAUSED,
            ])
            ->exists();

        abort_unless($managed, 404);

        $this->property = $record->load([
            'propertyType',
            'city',
            'district',
            'neighborhood',
            'units.currentTenancy.tenant',
            'managementAgreements.propertyOwner.user',
            'managementAgreements.manager',
        ]);

        $this->activeAgreements = $this->property->managementAgreements
            ->whereIn('status', [
                PropertyManagementAgreement::STATUS_ACTIVE,
                PropertyManagementAgreement::STATUS_PAUSED,
            ])
            ->values();

        $this->totalUnits = $this->property->units->count();
        $this->occupiedUnits = $this->property->units->filter(fn ($unit): bool => $unit->isOccupied())->count();
        $this->vacantUnits = max(0, $this->totalUnits - $this->occupiedUnits);
        $this->activeTenancies = $this->property->units->filter(fn ($unit): bool => $unit->currentTenancy !== null)->count();
        $this->occupancyRate = $this->totalUnits > 0
            ? round(($this->occupiedUnits / $this->totalUnits) * 100, 1)
            : 0.0;

        $this->upcomingServices = PropertyServiceSchedule::query()
            ->with(['service', 'unit'])
            ->where('property_id', $this->property->id)
            ->where('is_active', true)
            ->whereNotNull('next_due_at')
            ->orderBy('next_due_at')
            ->limit(8)
            ->get();

        $this->openMaintenance = MaintenanceRequest::query()
            ->with(['unit', 'service', 'vendor'])
            ->where('property_id', $this->property->id)
            ->whereNotIn('status', [MaintenanceRequest::STATUS_COMPLETED, MaintenanceRequest::STATUS_CANCELLED])
            ->latest('scheduled_at')
            ->limit(8)
            ->get();

        $this->recentExpenses = PropertyExpense::query()
            ->with(['unit', 'currency', 'vendor'])
            ->where('property_id', $this->property->id)
            ->latest('incurred_at')
            ->limit(8)
            ->get();

        $this->expiringDocuments = PropertyDocument::query()
            ->where('property_id', $this->property->id)
            ->where('status', PropertyDocument::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<=', today()->addDays(60))
            ->orderBy('expires_at')
            ->limit(8)
            ->get();

        $this->outstandingRent = (float) RentDueItem::query()
            ->whereHas('tenancy.unit', fn ($query) => $query->where('property_id', $this->property->id))
            ->whereIn('status', [RentDueItem::STATUS_DUE, RentDueItem::STATUS_PARTIAL, RentDueItem::STATUS_OVERDUE])
            ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as balance')
            ->value('balance');
    }

    public function getTitle(): string
    {
        return 'ملف العقار: '.($this->property->internal_code ?? $this->property->title_ar);
    }
}
