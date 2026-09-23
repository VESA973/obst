<?php

namespace App\Filament\Resources\ResourceFiles\Pages;

use App\Filament\Resources\ResourceFiles\ResourceFileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageResourceFiles extends ManageRecords
{
    protected static string $resource = ResourceFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nouvelle ressource'),
        ];
    }
}
