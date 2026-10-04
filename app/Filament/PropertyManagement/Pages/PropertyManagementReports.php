<?php

namespace App\Filament\PropertyManagement\Pages;

use App\Services\PropertyManagementReportService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class PropertyManagementReports extends Page
{
    protected string $view = 'filament.property-management.pages.reports';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'التقارير والمؤشرات';

    protected static ?string $title = 'التقارير والمؤشرات';

    protected static string|UnitEnum|null $navigationGroup = 'التقارير والإعدادات';

    protected static ?int $navigationSort = 1;

    public array $report = [];

    public function mount(PropertyManagementReportService $service): void
    {
        abort_unless(auth()->user()?->can('property_management_reports.view'), 403);

        $this->report = $service->dashboard();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('property_management_reports.view') ?? false;
    }
}
