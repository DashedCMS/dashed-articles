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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Dashed\DashedArticles\Models\Article;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Dashed\DashedCore\Classes\QueryHelpers\SearchQuery;
use Dashed\DashedCore\Filament\Concerns\HasVisitableTab;
use Dashed\DashedCore\Filament\Concerns\HasCustomBlocksTab;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Dashed\DashedCore\Classes\Actions\ActionGroups\ToolbarActions;
use Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages\EditArticle;
use Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages\ListArticles;
use Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages\CreateArticle;

class ArticleResource extends Resource
{
    use HasCustomBlocksTab;
    use HasVisitableTab;
    use Translatable;

    protected static ?string $model = Article::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-book-open';

    protected static string | UnitEnum | null $navigationGroup = 'Artikelen';

    protected static ?string $navigationLabel = 'Artikelen';

    protected static ?string $label = 'Artikel';

    protected static ?string $pluralLabel = 'Artikelen';

    protected static ?int $navigationSort = 5;

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'slug',
            'category.name',
            'content',
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
                            ->reactive()
                            ->lazy()
                            ->afterStateUpdated(function (Set $set, $state, $livewire) {
                                if ($livewire instanceof CreateArticle) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->unique('dashed__articles', 'slug', fn ($record) => $record)
                            ->helperText('Laat leeg om automatisch te laten genereren')
                            ->maxLength(255),
                        Select::make('author_id')
                            ->label('Auteur')
                            ->nullable()
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                            ->relationship('author', 'name'),
                        Select::make('category_id')
                            ->label('Categorie')
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                            ->relationship('category', 'name'),
                        Textarea::make('excerpt')
                            ->name('Korte tekst'),
                        mediaHelper()->field('image', 'Hoofd afbeelding', isImage: true)
                            ->helperText('Deze wordt gebruikt voor de overzichtspagina pagina'),
                        cms()->getFilamentBuilderBlock(),
                    ], static::customBlocksTab('articleBlocks')))
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
            ->columns(array_merge([
                TextColumn::make('name')
                    ->label('Naam')
                    ->sortable()
                    ->searchable(query: SearchQuery::make()),
                TextColumn::make('category.name')
                    ->label('Categorie')
                    ->sortable(),
                TextColumn::make('author.name')
                    ->label('Auteur')
                    ->sortable(),
            ], static::visitableTableColumns()))
            ->filters([
                SelectFilter::make('category')
                    ->label('Categorie')
                    ->searchable()
                    ->multiple()
                    ->relationship('category', 'name'),
                SelectFilter::make('author')
                    ->label('Auteur')
                    ->multiple()
                    ->relationship('author', 'name'),
            ])
            ->defaultSort('created_at', 'desc')
            ->reorderable('order')
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('category')
                    ->label('Categorie')
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->relationship('category', 'name'),
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
            'index' => ListArticles::route('/'),
            'create' => CreateArticle::route('/create'),
            'edit' => EditArticle::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
