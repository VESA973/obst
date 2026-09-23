<?php

namespace App\Filament\Resources\ArticleCategories\Tables;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\ArticleCategory;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ArticleCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('position')
            ->defaultSort(fn ($query) => $query->orderBy('section')->orderBy('position'))
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->square(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('section')
                    ->label('Rubrique')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ArticleCategory::SECTIONS[$state] ?? $state)
                    ->color(fn (string $state) => $state === 'public' ? 'info' : 'primary'),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('articles_count')
                    ->label('Articles')
                    ->counts('articles')
                    ->badge()
                    ->url(fn (ArticleCategory $record) => ArticleResource::getUrl('category', ['categorie' => $record->slug])),
                ToggleColumn::make('is_active')
                    ->label('Active'),
                TextColumn::make('position')
                    ->label('Ordre')
                    ->numeric()
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('section')
                    ->label('Rubrique')
                    ->options(ArticleCategory::SECTIONS),
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                Action::make('articles')
                    ->label('Articles')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->color('gray')
                    ->url(fn (ArticleCategory $record) => ArticleResource::getUrl('category', ['categorie' => $record->slug])),
                EditAction::make(),
                self::deleteAction(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalDescription('Seules les catégories vides seront supprimées. Réaffectez d\'abord les articles des autres.')
                        ->action(function (Collection $records): void {
                            // Suppression groupée limitée aux catégories vides.
                            [$empty, $filled] = $records->loadCount('articles')->partition(fn (ArticleCategory $category) => $category->articles_count === 0);

                            $empty->each->delete();

                            $notification = Notification::make()->title($empty->count().' catégorie(s) supprimée(s)');

                            if ($filled->isNotEmpty()) {
                                $notification->warning()->body('Non supprimées car elles contiennent des articles : '.$filled->pluck('name')->implode(', ').'.');
                            } else {
                                $notification->success();
                            }

                            $notification->send();
                        }),
                ]),
            ]);
    }

    /**
     * Suppression avec réaffectation obligatoire des articles si la catégorie n'est pas vide.
     */
    public static function deleteAction(): DeleteAction
    {
        return DeleteAction::make()
            ->modalDescription(fn (ArticleCategory $record) => $record->articles()->exists()
                ? 'Cette catégorie contient '.$record->articles()->count().' article(s). Choisissez la catégorie qui les recevra.'
                : 'Cette catégorie est vide et sera définitivement supprimée.')
            ->schema(fn (ArticleCategory $record) => $record->articles()->exists() ? [
                Select::make('reassign_to')
                    ->label('Réaffecter les articles vers')
                    ->options(ArticleCategory::query()->whereKeyNot($record->getKey())->ordered()->pluck('name', 'id'))
                    ->required()
                    ->native(false),
            ] : [])
            ->action(function (ArticleCategory $record, array $data, DeleteAction $action): void {
                DB::transaction(function () use ($record, $data): void {
                    if (filled($data['reassign_to'] ?? null)) {
                        $record->articles()->update(['article_category_id' => $data['reassign_to']]);
                    }

                    $record->delete();
                });

                $action->success();
            });
    }
}
