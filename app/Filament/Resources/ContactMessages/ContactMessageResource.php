<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\ManageContactMessages;
use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * Messages reçus via la page Contact (lecture, statut lu / non lu, réponse par e-mail).
 */
class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|UnitEnum|null $navigationGroup = 'Communauté';

    protected static ?string $navigationLabel = 'Messages';

    protected static ?int $navigationSort = 0;

    protected static ?string $modelLabel = 'message';

    protected static ?string $slug = 'messages';

    /** Nombre de messages non lus, affiché dans le menu. */
    public static function getNavigationBadge(): ?string
    {
        $unread = ContactMessage::query()->whereNull('read_at')->count();

        return $unread > 0 ? (string) $unread : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Messages non lus';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('name')->label('Nom'),
                TextEntry::make('email')->label('E-mail')->copyable(),
                TextEntry::make('phone')->label('Téléphone')->placeholder('—'),
                TextEntry::make('topic')->label('Motif')->formatStateUsing(fn (ContactMessage $record) => $record->topicLabel()),
                TextEntry::make('subject')->label('Sujet')->columnSpanFull(),
                TextEntry::make('message')->label('Message')->columnSpanFull()->extraAttributes(['style' => 'white-space: pre-line']),
                TextEntry::make('created_at')->label('Reçu le')->dateTime('d/m/Y à H:i'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordClasses(fn (ContactMessage $record) => $record->read_at ? null : 'font-semibold')
            ->columns([
                IconColumn::make('read_at')
                    ->label('Lu')
                    ->state(fn (ContactMessage $record) => $record->read_at !== null)
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedEnvelopeOpen)
                    ->falseIcon(Heroicon::Envelope)
                    ->trueColor('gray')
                    ->falseColor('primary'),
                TextColumn::make('name')->label('Nom')->searchable()->description(fn (ContactMessage $record) => $record->email),
                TextColumn::make('topic')->label('Motif')->badge()->formatStateUsing(fn (ContactMessage $record) => $record->topicLabel()),
                TextColumn::make('subject')->label('Sujet')->searchable()->limit(60)->wrap(),
                TextColumn::make('created_at')->label('Reçu le')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('read_at')->label('Statut')->nullable()->trueLabel('Lus')->falseLabel('Non lus'),
                SelectFilter::make('topic')->label('Motif')->options(ContactMessage::TOPICS),
            ])
            ->recordActions([
                // Ouvrir un message le marque comme lu.
                ViewAction::make()
                    ->label('Lire')
                    ->mountUsing(fn (ContactMessage $record) => $record->read_at ?? $record->update(['read_at' => now()])),
                Action::make('reply')
                    ->label('Répondre')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('gray')
                    ->url(fn (ContactMessage $record) => 'mailto:'.$record->email.'?subject='.rawurlencode('Re : '.$record->subject)),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markUnread')
                        ->label('Marquer comme non lus')
                        ->icon(Heroicon::Envelope)
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => $records->each->update(['read_at' => null])),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageContactMessages::route('/'),
        ];
    }
}
