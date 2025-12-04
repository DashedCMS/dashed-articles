<?php

namespace Dashed\DashedArticles\Filament\Resources;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Schemas\Components\Utilities\Set;
use Dashed\DashedArticles\Models\ArticleAuthor;
use Dashed\DashedCore\Classes\QueryHelpers\SearchQuery;
use Dashed\DashedCore\Filament\Concerns\HasVisitableTab;
use Dashed\DashedCore\Filament\Concerns\HasCustomBlocksTab;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Dashed\DashedCore\Classes\Actions\ActionGroups\ToolbarActions;
use Dashed\DashedArticles\Filament\Resources\AuthorResource\Pages\EditAuthor;
use Dashed\DashedArticles\Filament\Resources\AuthorResource\Pages\ListAuthor;
use Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages\EditArticle;
use Dashed\DashedArticles\Filament\Resources\AuthorResource\Pages\CreateAuthor;

class AuthorResource extends Resource
{
    use Translatable;
    use HasCustomBlocksTab;
    use HasVisitableTab;
    use Translatable;

    protected static ?string $model = ArticleAuthor::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static string | UnitEnum | null $navigationGroup = 'Artikelen';

    protected static ?string $navigationLabel = 'Auteurs';

    protected static ?string $label = 'Auteur';

    protected static ?string $pluralLabel = 'Auteurs';

    protected static ?int $navigationSort = 5;

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'slug',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Content')->columnSpanFull()
                    ->schema(array_merge([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->lazy()
                            ->afterStateUpdated(function (Set $set, $state, $livewire) {
                                if ($livewire instanceof CreateAuthor) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->unique('dashed__article_authors', 'slug', fn ($record) => $record)
                            ->helperText('Laat leeg om automatisch te laten genereren')
                            ->required()
                            ->maxLength(255),
                        mediaHelper()->field('image', 'Afbeelding', isImage: true),
                        cms()->getFilamentBuilderBlock(),
                    ], static::customBlocksTab('articleAuthorBlocks')))
                    ->columns(2),
                Section::make('Globale informatie')->columnSpanFull()
                    ->schema(static::publishTab())
                    ->collapsed(fn ($livewire) => $livewire instanceof EditArticle),
                Section::make('Meta data')->columnSpanFull()
                    ->schema(static::metadataTab()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Naam')
                    ->sortable()
                    ->searchable(query: SearchQuery::make()),
                TextColumn::make('articles_count')
                    ->label('Aantal artikelen')
                    ->sortable()
                    ->counts('articles'),
            ])
            ->defaultSort('created_at', 'desc')
            ->reorderable('order')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions(ToolbarActions::getActions());
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuthor::route('/'),
            'create' => CreateAuthor::route('/create'),
            'edit' => EditAuthor::route('/{record}/edit'),
        ];
    }
}
