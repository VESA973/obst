<?php

namespace App\Filament\Resources\NewsletterSubscribers;

use App\Filament\Resources\NewsletterSubscribers\Pages\ManageNewsletterSubscribers;
use App\Models\NewsletterSubscriber;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Abonnés à la lettre d'information (inscriptions depuis le site, lecture seule).
 */
class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static string|UnitEnum|null $navigationGroup = 'Communauté';

    protected static ?string $navigationLabel = 'Newsletter';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'abonné';

    protected static ?string $slug = 'newsletter';

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')->label('E-mail')->searchable()->copyable(),
                TextColumn::make('source')->label('Page d\'inscription')->limit(50)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Inscrit le')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->recordActions([
                DeleteAction::make()->label('Désinscrire'),
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
            'index' => ManageNewsletterSubscribers::route('/'),
        ];
    }
}
