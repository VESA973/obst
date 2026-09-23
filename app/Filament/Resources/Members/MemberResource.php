<?php

namespace App\Filament\Resources\Members;

use App\Filament\Resources\Members\Pages\ManageMembers;
use App\Models\Member;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static string|UnitEnum|null $navigationGroup = 'Communauté';

    protected static ?string $navigationLabel = 'Membres';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'membre';

    protected static ?string $slug = 'membres';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')->label('Nom')->required()->maxLength(255),
                TextInput::make('email')->label('E-mail')->email()->maxLength(255),
                TextInput::make('phone')->label('Téléphone')->tel()->maxLength(40),
                TextInput::make('role')->label('Rôle')->maxLength(120),
                TextInput::make('status')
                    ->label('Statut')
                    ->required()
                    ->maxLength(80)
                    ->default('actif')
                    ->datalist(fn () => Member::query()->distinct()->pluck('status')->all()),
                Textarea::make('notes')->label('Notes')->maxLength(2000)->rows(3)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->label('Nom')->searchable()->sortable()->weight('medium'),
                TextColumn::make('email')->label('E-mail')->searchable()->copyable(),
                TextColumn::make('phone')->label('Téléphone'),
                TextColumn::make('role')->label('Rôle')->toggleable(),
                TextColumn::make('status')->label('Statut')->badge(),
                TextColumn::make('created_at')->label('Ajouté le')->date('d/m/Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(fn () => Member::query()->distinct()->pluck('status', 'status')->all()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMembers::route('/'),
        ];
    }
}
