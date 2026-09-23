<?php

namespace App\Filament\Resources\Professionals;

use App\Filament\Resources\Professionals\Pages\ManageProfessionals;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Comptes de l'espace professionnel (membres et professionnels de santé).
 */
class ProfessionalResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|UnitEnum|null $navigationGroup = 'Communauté';

    protected static ?string $navigationLabel = 'Professionnels';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'compte professionnel';

    protected static ?string $pluralModelLabel = 'comptes professionnels';

    protected static ?string $slug = 'professionnels';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(fn (Builder $query) => $query->where('is_member', true)->orWhere('is_health_professional', true));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->label('Nom')->searchable()->weight('medium'),
                TextColumn::make('email')->label('E-mail')->searchable()->copyable(),
                ToggleColumn::make('is_member')->label('Accès membre'),
                ToggleColumn::make('is_health_professional')->label('Pro. de santé'),
                IconColumn::make('email_verified_at')
                    ->label('E-mail vérifié')
                    ->boolean()
                    ->state(fn (User $record) => $record->email_verified_at !== null),
                IconColumn::make('google_id')
                    ->label('Google')
                    ->boolean()
                    ->state(fn (User $record) => filled($record->google_id))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Inscrit le')->date('d/m/Y')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('email_verified_at')->label('E-mail vérifié')->nullable(),
                TernaryFilter::make('is_health_professional')->label('Professionnel de santé'),
            ])
            ->recordActions([
                Action::make('toggleVerification')
                    ->label(fn (User $record) => $record->email_verified_at ? 'Retirer la vérification' : 'Valider l\'e-mail')
                    ->icon(fn (User $record) => $record->email_verified_at ? Heroicon::OutlinedXCircle : Heroicon::OutlinedCheckBadge)
                    ->color(fn (User $record) => $record->email_verified_at ? 'gray' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->forceFill([
                        'email_verified_at' => $record->email_verified_at ? null : now(),
                    ])->save()),
                DeleteAction::make()
                    // Ni son propre compte, ni un administrateur.
                    ->hidden(fn (User $record) => $record->is_admin || $record->is(auth()->user())),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProfessionals::route('/'),
        ];
    }
}
