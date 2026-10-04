<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class PanelSwitcher extends Widget
{
    protected string $view = 'filament.widgets.panel-switcher';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -100;
}
