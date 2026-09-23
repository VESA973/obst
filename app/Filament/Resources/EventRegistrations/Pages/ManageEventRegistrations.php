<?php

namespace App\Filament\Resources\EventRegistrations\Pages;

use App\Filament\Exports\EventRegistrationsCsv;
use App\Filament\Resources\EventRegistrations\EventRegistrationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageEventRegistrations extends ManageRecords
{
    protected static string $resource = EventRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exporter en CSV')
                ->icon(Heroicon::OutlinedArrowDownTray)
                // Exporte les inscriptions correspondant aux filtres et à la recherche en cours.
                ->action(fn () => EventRegistrationsCsv::download($this->getFilteredTableQuery())),
        ];
    }
}
