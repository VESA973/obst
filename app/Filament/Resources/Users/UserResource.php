<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Password;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|UnitEnum|null $navigationGroup = 'Paramètres';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'utilisateur';

    protected static ?string $slug = 'utilisateurs';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')->label('Nom')->required()->maxLength(255),
                TextInput::make('email')->label('E-mail')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label('Mot de passe')
                    ->password()
                    ->revealable()
                    ->rule(Password::min(8))
                    // Obligatoire à la création, facultatif ensuite (vide = inchangé).
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->helperText(fn (string $operation) => $operation === 'edit' ? 'Laissez vide pour conserver le mot de passe actuel.' : null),
                Toggle::make('is_admin')
                    ->label('Administrateur')
                    // Un administrateur ne peut pas se retirer ses propres droits.
                    ->disabled(fn (?User $record) => $record?->is(auth()->user()) ?? false),
                Toggle::make('is_member')->label('Membre'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort(fn ($query) => $query->orderByDesc('is_admin')->orderBy('name'))
            ->columns([
                TextColumn::make('name')->label('Nom')->searchable()->weight('medium'),
                TextColumn::make('email')->label('E-mail')->searchable()->copyable(),
                IconColumn::make('is_admin')->label('Admin')->boolean(),
                IconColumn::make('is_member')->label('Membre')->boolean(),
                TextColumn::make('created_at')->label('Créé le')->date('d/m/Y')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_admin')->label('Administrateur'),
                TernaryFilter::make('is_member')->label('Membre'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (User $record) => $record->is(auth()->user())),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
