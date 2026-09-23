<?php

namespace App\Filament\Resources\ResourceFiles;

use App\Filament\Resources\ResourceFiles\Pages\ManageResourceFiles;
use App\Models\ResourceFile;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class ResourceFileResource extends Resource
{
    public const AUDIENCES = [
        'public' => 'Grand public',
        'pro' => 'Professionnels',
    ];

    protected static ?string $model = ResourceFile::class;

    protected static string|UnitEnum|null $navigationGroup = 'Contenus';

    protected static ?string $navigationLabel = 'Ressources';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'ressource';

    protected static ?string $slug = 'ressources';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')->label('Titre')->required()->maxLength(255),
                Select::make('audience')
                    ->label('Public visé')
                    ->options(self::AUDIENCES)
                    ->default('public')
                    ->required()
                    ->native(false),
                TextInput::make('category')
                    ->label('Catégorie')
                    ->maxLength(120)
                    ->datalist(fn () => ResourceFile::query()->whereNotNull('category')->distinct()->pluck('category')->all()),
                FileUpload::make('path')
                    ->label('Fichier')
                    ->disk('public')
                    ->directory('resources')
                    ->storeFileNamesIn('original_name')
                    ->maxSize(10240)
                    ->required(),
                Textarea::make('description')->label('Description')->maxLength(2000)->rows(3)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->weight('medium')
                    ->description(fn (ResourceFile $record) => $record->original_name),
                TextColumn::make('audience')
                    ->label('Public')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => self::AUDIENCES[$state] ?? $state)
                    ->color(fn (string $state) => $state === 'pro' ? 'warning' : 'info'),
                TextColumn::make('category')->label('Catégorie')->placeholder('—'),
                TextColumn::make('extension')
                    ->label('Format')
                    ->state(fn (ResourceFile $record) => strtoupper(pathinfo($record->path, PATHINFO_EXTENSION)))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('created_at')->label('Ajouté le')->date('d/m/Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('audience')->label('Public')->options(self::AUDIENCES),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Ouvrir')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->url(fn (ResourceFile $record) => Storage::disk('public')->url($record->path), shouldOpenInNewTab: true),
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
            'index' => ManageResourceFiles::route('/'),
        ];
    }
}
