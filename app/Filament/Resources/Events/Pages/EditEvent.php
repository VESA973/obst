<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Exports\EventRegistrationsCsv;
use App\Filament\Resources\Events\EventResource;
use App\Models\Event;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exporter les inscrits')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->action(fn (Event $record) => EventRegistrationsCsv::download(
                    $record->registrations()->getQuery(),
                    'inscriptions-'.Str::slug($record->title).'.csv',
                )),
            DeleteAction::make(),
        ];
    }
}
