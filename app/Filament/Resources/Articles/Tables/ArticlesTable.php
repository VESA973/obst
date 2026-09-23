<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['category', 'author']))
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->square(),
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Article $record) => $record->source_name)
                    ->wrap(),
                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('current_status')
                    ->label('Statut')
                    ->badge(),
                TextColumn::make('published_at')
                    ->label('Publication')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('author.name')
                    ->label('Auteur')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ArticleStatus::class)
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        ArticleStatus::Draft->value => $query->draft(),
                        ArticleStatus::Scheduled->value => $query->scheduled(),
                        ArticleStatus::Published->value => $query->visible(),
                        default => $query,
                    }),
                SelectFilter::make('article_category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name'),
                Filter::make('periode')
                    ->label('Période')
                    ->schema([
                        DatePicker::make('from')->label('Publié à partir du'),
                        DatePicker::make('until')->label('Publié jusqu\'au'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('published_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date) => $query->whereDate('published_at', '<=', $date)))
                    ->indicateUsing(fn (array $data) => array_filter([
                        filled($data['from'] ?? null) ? 'Depuis le '.date('d/m/Y', strtotime($data['from'])) : null,
                        filled($data['until'] ?? null) ? 'Jusqu\'au '.date('d/m/Y', strtotime($data['until'])) : null,
                    ])),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label('Publier')
                        ->icon(Heroicon::OutlinedEye)
                        ->color('success')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $records->each(function (Article $article): void {
                                $article->status = ArticleStatus::Published;
                                // Une date future est ramenée à maintenant pour publier immédiatement.
                                if ($article->published_at?->isFuture()) {
                                    $article->published_at = now();
                                }
                                $article->save();
                            });

                            Notification::make()->title($records->count().' article(s) publié(s)')->success()->send();
                        }),
                    BulkAction::make('unpublish')
                        ->label('Dépublier')
                        ->icon(Heroicon::OutlinedEyeSlash)
                        ->color('warning')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $records->each(fn (Article $article) => $article->update(['status' => ArticleStatus::Draft]));

                            Notification::make()->title($records->count().' article(s) repassé(s) en brouillon')->success()->send();
                        }),
                    BulkAction::make('move')
                        ->label('Déplacer vers une catégorie')
                        ->icon(Heroicon::OutlinedArrowRightCircle)
                        ->schema([
                            Select::make('article_category_id')
                                ->label('Catégorie de destination')
                                ->options(fn () => ArticleCategory::query()->ordered()->pluck('name', 'id'))
                                ->required()
                                ->native(false),
                        ])
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records, array $data): void {
                            DB::transaction(fn () => $records->each(
                                fn (Article $article) => $article->update(['article_category_id' => $data['article_category_id']])
                            ));

                            $category = ArticleCategory::find($data['article_category_id']);

                            Notification::make()
                                ->title($records->count().' article(s) déplacé(s) vers « '.$category?->name.' »')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
