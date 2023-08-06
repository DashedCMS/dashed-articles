<?php

namespace Dashed\DashedArticles\Filament\Resources;

use Closure;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Str;
use Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages\CreateArticle;
use Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages\EditArticle;
use Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages\ListArticles;
use Dashed\DashedArticles\Models\Article;
use Dashed\DashedCore\Filament\Concerns\HasCustomBlocksTab;
use Dashed\DashedCore\Filament\Concerns\HasVisitableTab;

class ArticleResource extends Resource
{
    use Translatable;
    use HasVisitableTab;
    use HasCustomBlocksTab;

    protected static ?string $model = Article::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Artikelen';
    protected static ?string $navigationLabel = 'Artikelen';
    protected static ?string $label = 'Artikel';
    protected static ?string $pluralLabel = 'Artikelen';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'slug',
            'category.name',
            'content',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'default' => 1,
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 1,
                    'xl' => 6,
                    '2xl' => 6,
                ])->schema([
                    Section::make('Content')
                        ->schema(array_merge([
                            TextInput::make('name')
                                ->label('Name')
                                ->required()
                                ->rules([
                                    'max:255',
                                ])
                                ->reactive()
                                ->afterStateUpdated(function (Closure $set, $state, $livewire) {
                                    if ($livewire instanceof CreateArticle) {
                                        $set('slug', Str::slug($state));
                                    }
                                }),
                            TextInput::make('slug')
                                ->label('Slug')
                                ->unique('dashed__articles', 'slug', fn ($record) => $record)
                                ->helperText('Laat leeg om automatisch te laten genereren')
                                ->rules([
                                    'max:255',
                                ]),
                            Select::make('author_id')
                                ->label('Auteur')
                                ->nullable()
                                ->relationship('author', 'name'),
                            Select::make('category_id')
                                ->label('Categorie')
                                ->nullable()
                                ->relationship('category', 'name'),
                            Builder::make('content')
                                ->blocks(cms()->builder('blocks'))
                                ->withBlockLabels()
                                ->columnSpan([
                                    'default' => 1,
                                    'sm' => 1,
                                    'md' => 1,
                                    'lg' => 1,
                                    'xl' => 2,
                                    '2xl' => 2,
                                ]),
                        ], static::customBlocksTab(cms()->builder('articleBlocks'))))
                        ->columns(2)
                        ->columnSpan([
                                        'default' => 1,
                                        'sm' => 1,
                                        'md' => 1,
                                        'lg' => 1,
                                        'xl' => 4,
                                        '2xl' => 4,
                            ]),
                    Grid::make([
                        'default' => 1,
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 1,
                        'xl' => 2,
                        '2xl' => 2,
                    ])
                        ->schema([
                            Section::make('Globale informatie')
                                ->schema(static::publishTab())
                                ->collapsed(fn ($livewire) => $livewire instanceof EditArticle)
                                ->columnSpan([
                                    'default' => 1,
                                    'sm' => 1,
                                    'md' => 1,
                                    'lg' => 1,
                                    'xl' => 2,
                                    '2xl' => 2,
                                ]),
                            Section::make('Meta data')
                                ->schema(static::metadataTab())
                                ->columnSpan([
                                    'default' => 1,
                                    'sm' => 1,
                                    'md' => 1,
                                    'lg' => 1,
                                    'xl' => 2,
                                    '2xl' => 2,
                                ]),
                        ])
                        ->columnSpan([
                            'default' => 1,
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 1,
                            'xl' => 2,
                            '2xl' => 2,
                        ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(array_merge([
                TextColumn::make('name')
                    ->label('Naam')
                    ->sortable()
                    ->searchable([
                        'name',
                        'slug',
                        'content',
                    ]),
                TextColumn::make('category')
                    ->label('Categorie')
                    ->getStateUsing(fn ($record) => $record->category->name ?? '-')
                    ->searchable([
                        'content',
                    ]),
                TextColumn::make('author')
                    ->label('Auteur')
                    ->getStateUsing(fn ($record) => $record->author->name ?? '-'),
            ], static::visitableTableColumns()))
            ->filters([
                SelectFilter::make('category')
                    ->label('Categorie')
                    ->multiple()
                    ->relationship('category', 'name'),
                SelectFilter::make('author')
                    ->label('Auteur')
                    ->multiple()
                    ->relationship('author', 'name'),


            ]);
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
}
