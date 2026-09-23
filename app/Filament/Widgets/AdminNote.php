<?php

namespace App\Filament\Widgets;

use App\Models\SiteSetting;
use Filament\Widgets\Widget;

/**
 * Affiche la note interne et l'état de la maintenance sur le tableau de bord.
 */
class AdminNote extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.admin-note';

    public static function canView(): bool
    {
        return filled(SiteSetting::getValue('admin_note')) || SiteSetting::maintenanceEnabled();
    }

    protected function getViewData(): array
    {
        return [
            'note' => SiteSetting::getValue('admin_note'),
            'maintenance' => SiteSetting::maintenanceEnabled(),
        ];
    }
}
