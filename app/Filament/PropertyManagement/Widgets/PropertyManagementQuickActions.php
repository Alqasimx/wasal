<?php

namespace App\Filament\PropertyManagement\Widgets;

use Filament\Widgets\Widget;

class PropertyManagementQuickActions extends Widget
{
    protected string $view = 'filament.property-management.widgets.quick-actions';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';
}
