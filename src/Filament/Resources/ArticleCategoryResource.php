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
use Filament\Tables\Filters\SelectFilter;
use Filament\Schemas\Components\Utilities\Set;
use Dashed\DashedArticles\Models\ArticleCategory;
use Dashed\DashedCore\Classes\QueryHelpers\SearchQuery;
use Dashed\DashedCore\Filament\Concerns\HasVisitableTab;
use Dashed\DashedCore\Filament\Concerns\HasCustomBlocksTab;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Dashed\DashedCore\Classes\Actions\ActionGroups\ToolbarActions;
use Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages\EditArticle;
use Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource\Pages\EditArticleCategory;
use Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource\Pages\CreateArticleCategory;
use Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource\Pages\ListArticleCategories;

class ArticleCategoryResource extends Resource
{
    use HasCustomBlocksTab;
    use HasVisitableTab;
    use Translatable;

    protected static ?string $model = ArticleCategory::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string | UnitEnum | null $navigationGroup = 'Artikelen';

    protected static ?string $navigationLabel = 'Categorieën';

    protected static ?string $label = 'Categorie';

    protected static ?string $pluralLabel = 'Categorieën';

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
                                if ($livewire instanceof CreateArticleCategory) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->unique('dashed__article_categories', 'slug', fn ($record) => $record)
                            ->helperText('Laat leeg om automatisch te laten genereren')
                            ->required()
                            ->maxLength(255),
                        cms()->getFilamentBuilderBlock(),
                    ], static::customBlocksTab('articleCategoryBlocks')))
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
            ], static::visitableTableColumns()))
            ->filters([
                SelectFilter::make('parent')
                    ->label('Bovenliggend item')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->relationship('parent', 'name'),
            ])
            ->reorderable('order')
            ->recordActions([
                EditAction::make()
                    ->button(),
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
            'index' => ListArticleCategories::route('/'),
            'create' => CreateArticleCategory::route('/create'),
            'edit' => EditArticleCategory::route('/{record}/edit'),
        ];
    }
}
