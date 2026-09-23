<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Support\GoogleOAuthConfigurator;
use Filament\Actions\Action;
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
 * Réglages généraux du site (maintenance, e-mails, connexion Google), stockés dans site_settings.
 *
 * @property-read Schema $form
 */
class SiteConfiguration extends Page
{
    /** Réglages texte simples, avec leur valeur par défaut. */
    private const DEFAULTS = [
        'maintenance_message' => 'Le site est temporairement en maintenance. Merci de revenir dans quelques instants.',
        'admin_note' => '',
        'smtp_mailer' => 'smtp',
        'smtp_host' => '',
        'smtp_port' => '465',
        'smtp_encryption' => 'ssl',
        'smtp_username' => '',
        'smtp_from_address' => '',
        'smtp_from_name' => 'La Quinzaine Obstétricale',
        'google_client_id' => '',
    ];

    protected static string|UnitEnum|null $navigationGroup = 'Paramètres';

    protected static ?string $navigationLabel = 'Configuration';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Configuration du site';

    protected static ?string $slug = 'configuration';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $values = collect(self::DEFAULTS)->map(fn (string $default, string $key) => SiteSetting::getValue($key, $default))->all();

        $this->form->fill([
            ...$values,
            'maintenance_enabled' => SiteSetting::getValue('maintenance_enabled', '0') === '1',
            'google_login_enabled' => SiteSetting::getValue('google_login_enabled', '0') === '1',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Maintenance')
                    ->description('Pendant la maintenance, seuls les administrateurs voient le site.')
                    ->schema([
                        Toggle::make('maintenance_enabled')->label('Activer le mode maintenance'),
                        Textarea::make('maintenance_message')->label('Message affiché aux visiteurs')->required()->maxLength(500)->rows(2),
                        Textarea::make('admin_note')->label('Note interne')->helperText('Visible uniquement dans l\'administration.')->maxLength(1000)->rows(3),
                    ]),
                Section::make('Envoi des e-mails')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Select::make('smtp_mailer')
                            ->label('Mode d\'envoi')
                            ->options(['smtp' => 'SMTP', 'log' => 'Journal (aucun envoi, pour les tests)'])
                            ->required(),
                        TextInput::make('smtp_host')->label('Serveur SMTP')->maxLength(255),
                        TextInput::make('smtp_port')->label('Port')->integer()->minValue(1)->maxValue(65535),
                        Select::make('smtp_encryption')->label('Chiffrement')->options(['ssl' => 'SSL', 'tls' => 'TLS', '' => 'Aucun']),
                        TextInput::make('smtp_username')->label('Identifiant')->maxLength(255),
                        TextInput::make('smtp_password')
                            ->label('Mot de passe')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->placeholder(fn () => filled(SiteSetting::getValue('smtp_password')) ? '•••••••• (enregistré)' : null)
                            ->helperText('Laissez vide pour conserver le mot de passe enregistré.'),
                        TextInput::make('smtp_from_address')->label('Adresse d\'expédition')->email()->maxLength(255),
                        TextInput::make('smtp_from_name')->label('Nom d\'expédition')->maxLength(255),
                    ]),
                Section::make('Connexion avec Google')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Toggle::make('google_login_enabled')->label('Autoriser la connexion Google')->columnSpanFull(),
                        TextInput::make('google_client_id')->label('Client ID')->maxLength(255),
                        TextInput::make('google_client_secret')
                            ->label('Client secret')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->placeholder(fn () => filled(SiteSetting::getValue('google_client_secret')) ? '•••••••• (enregistré)' : null)
                            ->helperText('Laissez vide pour conserver le secret enregistré.'),
                        TextInput::make('google_redirect_uri')
                            ->label('URI de redirection à déclarer chez Google')
                            ->default(fn () => GoogleOAuthConfigurator::redirectUri())
                            ->formatStateUsing(fn () => GoogleOAuthConfigurator::redirectUri())
                            ->readOnly()
                            ->copyable()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),
            ]);
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
        $data = $this->form->getState();

        SiteSetting::setValue('maintenance_enabled', $data['maintenance_enabled'] ? '1' : '0');
        SiteSetting::setValue('google_login_enabled', $data['google_login_enabled'] ? '1' : '0');

        foreach (array_keys(self::DEFAULTS) as $key) {
            SiteSetting::setValue($key, (string) ($data[$key] ?? ''));
        }

        // Les secrets ne sont remplacés que s'ils sont saisis.
        foreach (['smtp_password', 'google_client_secret'] as $secret) {
            if (filled($data[$secret] ?? null)) {
                SiteSetting::setValue($secret, $data[$secret]);
            }
        }

        $this->form->fill([...$data, 'smtp_password' => null, 'google_client_secret' => null]);

        Notification::make()->title('Configuration enregistrée')->success()->send();
    }
}
