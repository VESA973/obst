<?php

namespace App\Filament\Resources\ArticleCategories;

use App\Filament\Resources\ArticleCategories\Pages\CreateArticleCategory;
use App\Filament\Resources\ArticleCategories\Pages\EditArticleCategory;
use App\Filament\Resources\ArticleCategories\Pages\ListArticleCategories;
use App\Filament\Resources\ArticleCategories\Schemas\ArticleCategoryForm;
use App\Filament\Resources\ArticleCategories\Tables\ArticleCategoriesTable;
use App\Models\ArticleCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ArticleCategoryResource extends Resource
{
    protected static ?string $model = ArticleCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolderPlus;

    protected static string|UnitEnum|null $navigationGroup = 'Gestion des articles';

    protected static ?string $navigationLabel = 'Création des catégories';

    protected static ?int $navigationSort = 0;

    protected static ?string $modelLabel = 'catégorie';

    protected static ?string $pluralModelLabel = 'catégories';

    protected static ?string $slug = 'categories';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ArticleCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArticleCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticleCategories::route('/'),
            'create' => CreateArticleCategory::route('/creer'),
            'edit' => EditArticleCategory::route('/{record}/modifier'),
        ];
    }
}
