<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    self::contentSection(),
                    self::photosSection(),
                    self::seoSection(),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    self::publicationSection(),
                    self::imageSection(),
                ])->columnSpan(['lg' => 1]),
            ]);
    }

    private static function contentSection(): Section
    {
        return Section::make('Contenu')
            ->schema([
                TextInput::make('title')
                    ->label('Titre')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    // Le slug suit le titre tant qu'il n'a pas été modifié à la main.
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                        if (blank($get('slug')) || $get('slug') === Str::slug((string) $old)) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')
                    ->label('Adresse (slug)')
                    ->helperText('Généré depuis le titre. Le modifier après publication casse les liens existants.')
                    ->required()
                    ->maxLength(255)
                    ->alphaDash()
                    ->unique(ignoreRecord: true),
                RichEditor::make('body')
                    ->label('Article')
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'link'],
                        ['h2', 'h3', 'blockquote'],
                        ['bulletList', 'orderedList'],
                        ['undo', 'redo'],
                    ]),
                Textarea::make('excerpt')
                    ->label('Résumé court')
                    ->helperText('Affiché dans les listes d\'articles.')
                    ->rows(3)
                    ->maxLength(500),
            ]);
    }

    private static function publicationSection(): Section
    {
        return Section::make('Publication')
            ->schema([
                ToggleButtons::make('status')
                    ->label('Statut')
                    ->options(ArticleStatus::class)
                    ->default(ArticleStatus::Published)
                    ->inline()
                    ->required()
                    ->live(),
                DateTimePicker::make('published_at')
                    ->label(fn (Get $get) => self::status($get) === ArticleStatus::Scheduled ? 'Publier le' : 'Date de publication')
                    ->helperText(fn (Get $get) => self::status($get) === ArticleStatus::Scheduled ? null : 'Laissez vide pour publier maintenant.')
                    ->seconds(false)
                    ->native(false)
                    ->visible(fn (Get $get) => self::status($get) !== ArticleStatus::Draft)
                    ->required(fn (Get $get) => self::status($get) === ArticleStatus::Scheduled)
                    ->after(fn (Get $get) => self::status($get) === ArticleStatus::Scheduled ? 'now' : null),
                // Rubrique d'abord : elle filtre la liste des catégories proposées.
                Select::make('rubrique')
                    ->label('Rubrique')
                    ->options(ArticleCategory::SECTIONS)
                    ->required()
                    ->native(false)
                    ->live()
                    // Champ de formulaire seulement : la rubrique découle de la catégorie.
                    ->dehydrated(false)
                    ->afterStateHydrated(fn (Select $component, ?Article $record) => $component->state($record?->category?->section))
                    ->afterStateUpdated(function (Get $get, Set $set): void {
                        $category = ArticleCategory::find($get('article_category_id'));

                        if ($category && $category->section !== $get('rubrique')) {
                            $set('article_category_id', null);
                        }
                    }),
                Select::make('article_category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name', fn (Builder $query, Get $get) => $query->where('section', $get('rubrique'))->ordered())
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled(fn (Get $get) => blank($get('rubrique')))
                    ->placeholder(fn (Get $get) => blank($get('rubrique')) ? 'Choisissez d\'abord une rubrique' : 'Choisir une catégorie')
                    ->helperText('Changer la catégorie déplace l\'article.'),
                TextInput::make('source_name')
                    ->label('Source externe')
                    ->maxLength(120),
                TextInput::make('external_url')
                    ->label('Lien vers l\'article externe')
                    ->url()
                    ->maxLength(255),
            ]);
    }

    private static function imageSection(): Section
    {
        return Section::make('Image à la une')
            ->schema([
                FileUpload::make('image_path')
                    ->hiddenLabel()
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('articles')
                    ->maxSize(20480),
            ]);
    }

    private static function photosSection(): Section
    {
        return Section::make('Photos supplémentaires')
            ->collapsible()
            ->schema([
                Repeater::make('assets')
                    ->hiddenLabel()
                    ->relationship()
                    ->addActionLabel('Ajouter une photo')
                    ->grid(3)
                    ->defaultItems(0)
                    ->schema([
                        Hidden::make('type')->default('photo'),
                        FileUpload::make('path')
                            ->hiddenLabel()
                            ->image()
                            ->disk('public')
                            ->directory('articles/photos')
                            ->storeFileNamesIn('original_name')
                            ->maxSize(20480)
                            ->required(),
                    ]),
            ]);
    }

    private static function seoSection(): Section
    {
        return Section::make('Référencement (SEO)')
            ->collapsible()
            ->collapsed()
            ->schema([
                TextInput::make('meta_title')
                    ->label('Titre SEO')
                    ->helperText('Laissez vide pour reprendre le titre de l\'article.')
                    ->maxLength(70)
                    ->live(debounce: 500)
                    ->hint(fn (?string $state) => mb_strlen((string) $state).' / 70'),
                Textarea::make('meta_description')
                    ->label('Description SEO')
                    ->helperText('Laissez vide pour reprendre le résumé.')
                    ->rows(2)
                    ->maxLength(160)
                    ->live(debounce: 500)
                    ->hint(fn (?string $state) => mb_strlen((string) $state).' / 160'),
            ]);
    }

    /**
     * Statut courant du formulaire, qu'il soit une instance d'enum ou une chaîne.
     */
    private static function status(Get $get): ?ArticleStatus
    {
        $status = $get('status');

        return $status instanceof ArticleStatus ? $status : ArticleStatus::tryFrom((string) $status);
    }
}
