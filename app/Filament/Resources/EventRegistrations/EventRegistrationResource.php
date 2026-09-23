<?php

namespace App\Filament\Resources\EventRegistrations;

use App\Filament\Resources\EventRegistrations\Pages\ManageEventRegistrations;
use App\Filament\Resources\Events\EventResource;
use App\Models\EventRegistration;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Vue d'ensemble de toutes les inscriptions, tous événements confondus.
 */
class EventRegistrationResource extends Resource
{
    protected static ?string $model = EventRegistration::class;

    protected static string|UnitEnum|null $navigationGroup = 'Agenda';

    protected static ?string $navigationLabel = 'Inscriptions';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'inscription';

    protected static ?string $slug = 'inscriptions';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('event'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('event.title')
                    ->label('Événement')
                    ->url(fn (EventRegistration $record) => $record->event ? EventResource::getUrl('edit', ['record' => $record->event]) : null)
                    ->wrap(),
                TextColumn::make('event.event_date')->label('Date')->date('d/m/Y'),
                TextColumn::make('name')->label('Nom')->searchable(),
                TextColumn::make('email')->label('E-mail')->searchable()->copyable(),
                TextColumn::make('phone')->label('Téléphone'),
                TextColumn::make('created_at')->label('Inscrit le')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('event_id')->label('Événement')->relationship('event', 'title')->searchable()->preload(),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageEventRegistrations::route('/'),
        ];
    }
}
