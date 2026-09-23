<?php

namespace App\Filament\Resources\ArticleCategories\Schemas;

use App\Models\ArticleCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Catégorie')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(120)
                            ->unique(ignoreRecord: true)
                            ->live(onBlur: true)
                            // Le slug suit le nom tant qu'il n'a pas été modifié à la main.
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                                if (blank($get('slug')) || $get('slug') === Str::slug((string) $old)) {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Adresse (slug)')
                            ->helperText('Utilisé dans l\'URL publique. Le modifier casse les liens existants.')
                            ->required()
                            ->maxLength(140)
                            ->alphaDash()
                            ->unique(ignoreRecord: true),
                        Select::make('section')
                            ->label('Rubrique du site')
                            ->options(ArticleCategory::SECTIONS)
                            // Pré-rempli depuis l'onglet de rubrique (?rubrique=…).
                            ->default(fn () => array_key_exists((string) request()->query('rubrique'), ArticleCategory::SECTIONS) ? request()->query('rubrique') : 'news')
                            ->required()
                            ->native(false),
                        TextInput::make('title')
                            ->label('Titre affiché')
                            ->helperText('Laissez vide pour reprendre le nom.')
                            ->maxLength(180),
                        Textarea::make('description')
                            ->label('Description')
                            ->maxLength(1000)
                            ->rows(3)
                            ->columnSpanFull(),
                        FileUpload::make('image_path')
                            ->label('Image d\'illustration')
                            ->image()
                            ->disk('public')
                            ->directory('article-categories')
                            ->maxSize(20480),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Une catégorie inactive disparaît de la navigation et du site public.')
                            ->default(true),
                    ]),
            ]);
    }
}
