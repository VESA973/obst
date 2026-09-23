<?php

namespace App\Filament\Pages;

use App\Models\AssociationPageImage;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use UnitEnum;

/**
 * Photos de la page publique « L'association » (/qui-sommes-nous) :
 * quatre emplacements fixes et une galerie réordonnable.
 *
 * @property-read Schema $form
 */
class AssociationPage extends Page
{
    /** Formats acceptés et taille maximale (Ko). */
    private const MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    private const MAX_SIZE = 5120;

    protected static string|UnitEnum|null $navigationGroup = 'Contenus';

    protected static ?string $navigationLabel = 'Page L\'association';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Photos de la page « L\'association »';

    protected static ?string $slug = 'page-association';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $slots = AssociationPageImage::query()->whereIn('slot', array_keys(AssociationPageImage::SLOTS))->get()->keyBy('slot');

        $this->form->fill([
            'slots' => collect(AssociationPageImage::SLOTS)->mapWithKeys(fn (array $config, string $slot) => [
                $slot => $slots->get($slot)?->only(['path', 'alt', 'caption']) ?? ['path' => null, 'alt' => null, 'caption' => null],
            ])->all(),
            'gallery' => AssociationPageImage::query()->gallery()->get()
                ->map(fn (AssociationPageImage $image) => $image->only(['id', 'path', 'alt', 'caption', 'is_visible']))
                ->all(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                ...collect(AssociationPageImage::SLOTS)->map(fn (array $config, string $slot) => Section::make($config['label'])
                    ->description("Format recadré en {$config['ratio']}. Sans image, un visuel de remplacement est affiché.")
                    ->collapsible()
                    ->columns(2)
                    ->statePath("slots.{$slot}")
                    ->schema([
                        self::imageUpload('association', $config['ratio'])->columnSpanFull(),
                        ...self::textFields(),
                    ]))->values()->all(),

                Section::make('Galerie photos')
                    ->description('Glissez-déposez pour changer l\'ordre. Format libre ; les photos masquées restent enregistrées.')
                    ->collapsible()
                    ->schema([
                        Repeater::make('gallery')
                            ->hiddenLabel()
                            ->addActionLabel('Ajouter une photo')
                            ->reorderableWithDragAndDrop()
                            ->collapsible()
                            ->itemLabel(fn (array $state) => $state['alt'] ?? 'Nouvelle photo')
                            ->defaultItems(0)
                            ->columns(2)
                            ->schema([
                                Hidden::make('id'),
                                self::imageUpload('association/galerie')->required()->columnSpanFull(),
                                ...self::textFields(),
                                Toggle::make('is_visible')->label('Visible sur le site')->default(true),
                            ]),
                    ]),
            ]);
    }

    /**
     * Envoi d'image : éditeur intégré, recadrage imposé si un ratio est donné.
     */
    private static function imageUpload(string $directory, ?string $ratio = null): FileUpload
    {
        $upload = FileUpload::make('path')
            ->label('Image')
            ->image()
            ->imageEditor()
            ->disk('public')
            ->directory($directory)
            ->acceptedFileTypes(self::MIME_TYPES)
            ->maxSize(self::MAX_SIZE)
            ->helperText('JPG, PNG ou WebP, 5 Mo maximum.');

        return $ratio
            ? $upload->imageCropAspectRatio($ratio)->imageEditorAspectRatioOptions([$ratio])
            : $upload;
    }

    /**
     * Texte alternatif (obligatoire dès qu'une image est choisie) et légende.
     *
     * @return array<int, TextInput>
     */
    private static function textFields(): array
    {
        return [
            TextInput::make('alt')
                ->label('Texte alternatif')
                ->helperText('Décrit l\'image pour les personnes qui ne la voient pas.')
                ->maxLength(200)
                ->required(fn (Get $get) => filled($get('path'))),
            TextInput::make('caption')
                ->label('Légende (facultative)')
                ->maxLength(300),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Enregistrer')->submit('save')->keyBindings(['mod+s']),
                        Action::make('view')->label('Voir la page')->color('gray')->url(route('about'), shouldOpenInNewTab: true),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        DB::transaction(function () use ($data): void {
            // Emplacements fixes : créés, remplacés ou supprimés selon le formulaire.
            foreach ($data['slots'] as $slot => $values) {
                $image = AssociationPageImage::query()->firstWhere('slot', $slot);

                if (blank($values['path'] ?? null)) {
                    $image?->delete();

                    continue;
                }

                ($image ?? new AssociationPageImage(['slot' => $slot]))->fill($values)->save();
            }

            // Galerie : synchronisée avec la liste (ordre = position).
            $keptIds = [];

            foreach (array_values($data['gallery'] ?? []) as $position => $values) {
                $image = filled($values['id'] ?? null)
                    ? AssociationPageImage::query()->gallery()->find($values['id'])
                    : null;

                $image ??= new AssociationPageImage(['slot' => AssociationPageImage::GALLERY]);
                $image->fill([...collect($values)->except('id')->all(), 'position' => $position])->save();
                $keptIds[] = $image->id;
            }

            AssociationPageImage::query()->gallery()->whereKeyNot($keptIds)->get()->each->delete();
        });

        $this->mount();

        Notification::make()->title('Photos enregistrées')->success()->send();
    }
}
