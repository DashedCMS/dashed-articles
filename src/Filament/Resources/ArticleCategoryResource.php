<?php

namespace Dashed\DashedArticles\Filament\Resources;

use Closure;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;
use Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource\Pages\CreateArticleCategory;
use Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource\Pages\EditArticleCategory;
use Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource\Pages\ListArticleCategories;
use Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages\EditArticle;
use Dashed\DashedArticles\Models\ArticleCategory;
use Dashed\DashedCore\Filament\Concerns\HasVisitableTab;

class ArticleCategoryResource extends Resource
{
    use Translatable;
    use HasVisitableTab;

    protected static ?string $model = ArticleCategory::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $icon = 'heroicon-squares-2x2';
    protected static ?string $navigationGroup = 'Artikelen';
    protected static ?string $navigationLabel = 'Categorieën';
    protected static ?string $label = 'Categorie';
    protected static ?string $pluralLabel = 'Categorieën';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'slug',
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
                        ->schema([
                            TextInput::make('name')
                                ->label('Name')
                                ->required()
                                ->rules([
                                    'max:255',
                                ])
                                ->columnSpan([
                                    'default' => 2,
                                    'sm' => 2,
                                    'md' => 2,
                                    'lg' => 2,
                                    'xl' => 1,
                                    '2xl' => 1,
                                ])
                                ->reactive()
                                ->afterStateUpdated(function (Closure $set, $state, $livewire) {
                                    if ($livewire instanceof CreateArticleCategory) {
                                        $set('slug', Str::slug($state));
                                    }
                                }),
                            TextInput::make('slug')
                                ->label('Slug')
                                ->unique('dashed__article_categories', 'slug', fn ($record) => $record)
                                ->helperText('Laat leeg om automatisch te laten genereren')
                                ->required()
                                ->rules([
                                    'max:255',

                                ])
                                ->columnSpan([
                                    'default' => 2,
                                    'sm' => 2,
                                    'md' => 2,
                                    'lg' => 2,
                                    'xl' => 1,
                                    '2xl' => 1,
                                ]),
                            Builder::make('content')
                                ->blocks(cms()->builder('blocks'))
                                ->withBlockLabels()
                                ->columnSpan([
                                    'default' => 2,
                                    'sm' => 2,
                                    'md' => 2,
                                    'lg' => 2,
                                    'xl' => 2,
                                    '2xl' => 2,
                                ]),
                        ])
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
                    ])->schema([
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
                    ])->columnSpan([
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
            ], static::visitableTableColumns()))
            ->filters([
                //
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
            'index' => ListArticleCategories::route('/'),
            'create' => CreateArticleCategory::route('/create'),
            'edit' => EditArticleCategory::route('/{record}/edit'),
        ];
    }
}
