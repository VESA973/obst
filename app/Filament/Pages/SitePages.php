<?php

namespace App\Filament\Pages;

use App\Models\PageSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * En-têtes et entrées de menu des pages publiques (valeurs par défaut dans config/site_pages.php).
 *
 * @property-read Schema $form
 */
class SitePages extends Page
{
    private const FIELDS = ['menu_label', 'eyebrow', 'title', 'description', 'title_size', 'show_in_menu', 'hero_image_path'];

    protected static string|UnitEnum|null $navigationGroup = 'Contenus';

    protected static ?string $navigationLabel = 'Pages du site';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Pages du site';

    protected static ?string $slug = 'pages';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $stored = PageSetting::query()->get()->keyBy('page_key');

        $this->form->fill([
            'pages' => PageSetting::allConfigured()->map(fn (array $page, string $key) => [
                ...collect($page)->only(self::FIELDS)->all(),
                // Seules les images envoyées par l'administration sont modifiables ici.
                'hero_image_path' => $stored->get($key)?->hero_image_path,
            ])->all(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components(
                collect(PageSetting::defaults())->map(fn (array $default, string $key) => Section::make($default['menu_label'] ?? $key)
                    ->description(filled($default['route'] ?? null) ? '/'.ltrim(parse_url(route($default['route']), PHP_URL_PATH) ?? '', '/') : null)
                    ->collapsible()
                    ->collapsed()
                    ->columns(2)
                    ->statePath("pages.{$key}")
                    ->schema([
                        TextInput::make('menu_label')->label('Libellé du menu')->required()->maxLength(80),
                        Toggle::make('show_in_menu')->label('Afficher dans le menu')->inline(false),
                        TextInput::make('eyebrow')->label('Surtitre')->maxLength(120),
                        Select::make('title_size')
                            ->label('Taille du titre')
                            ->options(['small' => 'Titre discret', 'normal' => 'Titre normal', 'large' => 'Grand titre'])
                            ->required(),
                        TextInput::make('title')->label('Titre')->required()->maxLength(180)->columnSpanFull(),
                        Textarea::make('description')->label('Texte d\'introduction')->maxLength(600)->rows(3)->columnSpanFull(),
                        FileUpload::make('hero_image_path')
                            ->label('Image d\'en-tête')
                            ->helperText('Laissez vide pour garder l\'image par défaut du thème.')
                            ->image()
                            ->disk('public')
                            ->directory('pages/headers')
                            ->maxSize(20480)
                            ->columnSpanFull(),
                    ]))
                    ->values()
                    ->all(),
            );
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
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        foreach ($this->form->getState()['pages'] as $key => $attributes) {
            abort_unless(array_key_exists($key, PageSetting::defaults()), 404);

            PageSetting::query()->firstOrNew(['page_key' => $key])->fill($attributes)->save();
        }

        Notification::make()->title('Pages enregistrées')->success()->send();
    }
}
