<?php

namespace App\Filament\Resources\Events;

use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\RelationManagers\RegistrationsRelationManager;
use App\Models\Event;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|UnitEnum|null $navigationGroup = 'Agenda';

    protected static ?string $navigationLabel = 'Événements';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'événement';

    protected static ?string $slug = 'evenements';

    protected static ?string $recordTitleAttribute = 'title';

    /** Valeurs CSS object-position : les affiches sont surtout recadrées en hauteur. */
    private const FRAMING_OPTIONS = [
        '50% 0%' => 'Haut',
        '50% 25%' => 'Haut-milieu',
        '50% 50%' => 'Centre',
        '50% 75%' => 'Bas-milieu',
        '50% 100%' => 'Bas',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('Événement')
                        ->columns(2)
                        ->schema([
                            TextInput::make('title')->label('Titre')->required()->maxLength(255)->columnSpanFull(),
                            DatePicker::make('event_date')
                                ->label('Date principale')
                                ->helperText('Laissez vide pour reprendre la première date du programme.')
                                ->native(false),
                            TextInput::make('location')->label('Lieu')->maxLength(255),
                            Textarea::make('description')->label('Description')->maxLength(2000)->rows(4)->columnSpanFull(),
                        ]),
                    Section::make('Programme')
                        ->description('Une ligne par journée ou session.')
                        ->collapsible()
                        ->schema([
                            Repeater::make('schedule_items')
                                ->hiddenLabel()
                                ->addActionLabel('Ajouter une date')
                                ->defaultItems(0)
                                ->columns(4)
                                ->schema([
                                    TextInput::make('label')->label('Intitulé')->maxLength(120),
                                    DatePicker::make('date')->label('Date')->required()->native(false),
                                    TimePicker::make('start_time')->label('Début')->seconds(false),
                                    TimePicker::make('end_time')->label('Fin')->seconds(false)->after('start_time'),
                                ]),
                        ]),
                    Section::make('Photos et documents')
                        ->collapsible()
                        ->schema([
                            Repeater::make('assets')
                                ->hiddenLabel()
                                ->relationship()
                                ->addActionLabel('Ajouter un fichier')
                                ->defaultItems(0)
                                ->columns(3)
                                ->schema([
                                    Select::make('type')
                                        ->label('Type')
                                        ->options(['photo' => 'Photo', 'document' => 'Document'])
                                        ->default('photo')
                                        ->required()
                                        ->live(),
                                    TextInput::make('title')
                                        ->label('Titre du document')
                                        ->maxLength(255)
                                        ->visible(fn (Get $get) => $get('type') === 'document'),
                                    FileUpload::make('path')
                                        ->label('Fichier')
                                        ->disk('public')
                                        ->directory(fn (Get $get) => $get('type') === 'document' ? 'events/documents' : 'events/photos')
                                        ->storeFileNamesIn('original_name')
                                        ->maxSize(20480)
                                        ->required(),
                                ]),
                        ]),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make('Publication')
                        ->schema([
                            Toggle::make('is_published')->label('Publié')->default(true),
                        ]),
                    Section::make('Inscriptions')
                        ->schema([
                            Toggle::make('is_free_entry')
                                ->label('Événement libre')
                                ->helperText('Entrée libre et gratuite, sans inscription.')
                                ->live(),
                            Toggle::make('is_paid')
                                ->label('Événement payant')
                                ->helperText('Les inscriptions se font alors via le lien externe.')
                                ->hidden(fn (Get $get) => (bool) $get('is_free_entry'))
                                ->live(),
                            TextInput::make('registration_url')
                                ->label('Lien d\'inscription externe')
                                ->url()
                                ->maxLength(255)
                                ->hidden(fn (Get $get) => (bool) $get('is_free_entry'))
                                ->required(fn (Get $get) => (bool) $get('is_paid')),
                            TextInput::make('registration_capacity')
                                ->label('Nombre de places')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(10000)
                                ->hidden(fn (Get $get) => $get('is_paid') || $get('is_free_entry')),
                        ]),
                    Section::make('Affiche')
                        ->schema([
                            FileUpload::make('image_path')
                                ->hiddenLabel()
                                ->helperText('Image ou PDF.')
                                ->disk('public')
                                ->directory('events/flyers')
                                ->maxSize(20480),
                            Select::make('banner_position')
                                ->label('Cadrage de la bannière')
                                ->helperText('Partie de l\'image visible en haut de la page de l\'événement.')
                                ->options(self::FRAMING_OPTIONS)
                                ->placeholder('Centre'),
                            Select::make('flyer_position')
                                ->label('Cadrage des vignettes')
                                ->helperText('Partie de l\'image visible sur les cartes (accueil, agenda).')
                                ->options(self::FRAMING_OPTIONS)
                                ->placeholder('Centre'),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('registrations'))
            // Événements datés d'abord, du plus proche au plus lointain.
            ->defaultSort(fn ($query) => $query->orderByRaw('event_date IS NULL')->orderBy('event_date'))
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Affiche')
                    ->disk('public')
                    // Les affiches PDF n'ont pas d'aperçu.
                    ->state(fn (Event $record) => $record->flyer_is_image ? $record->image_path : null)
                    ->square(),
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->weight('medium')
                    ->description(fn (Event $record) => $record->location)
                    ->wrap(),
                TextColumn::make('event_date')->label('Date')->date('d/m/Y')->sortable()->placeholder('À définir'),
                TextColumn::make('registrations_count')
                    ->label('Inscrits')
                    ->badge()
                    ->formatStateUsing(fn (int $state, Event $record) => $record->registration_capacity ? "{$state} / {$record->registration_capacity}" : (string) $state),
                IconColumn::make('is_paid')->label('Payant')->boolean(),
                IconColumn::make('is_free_entry')->label('Libre')->boolean(),
                IconColumn::make('is_published')->label('Publié')->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Publié'),
                TernaryFilter::make('upcoming')
                    ->label('Période')
                    ->trueLabel('À venir')
                    ->falseLabel('Passés')
                    ->queries(
                        true: fn ($query) => $query->where(fn ($query) => $query->whereNull('event_date')->orWhereDate('event_date', '>=', today())),
                        false: fn ($query) => $query->whereDate('event_date', '<', today()),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RegistrationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvents::route('/'),
            'create' => CreateEvent::route('/creer'),
            'edit' => EditEvent::route('/{record}/modifier'),
        ];
    }
}
