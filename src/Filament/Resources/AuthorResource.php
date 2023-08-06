<?php

namespace Dashed\DashedArticles\Filament\Resources;

use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;
use Dashed\DashedArticles\Filament\Resources\AuthorResource\Pages\CreateAuthor;
use Dashed\DashedArticles\Filament\Resources\AuthorResource\Pages\EditAuthor;
use Dashed\DashedArticles\Filament\Resources\AuthorResource\Pages\ListAuthor;
use Dashed\DashedArticles\Models\Author;

class AuthorResource extends Resource
{
    use Translatable;

    protected static ?string $model = Author::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Artikelen';
    protected static ?string $navigationLabel = 'Auteurs';
    protected static ?string $label = 'Auteur';
    protected static ?string $pluralLabel = 'Auteurs';

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
                                if ($livewire instanceof CreateAuthor) {
                                    $set('slug', Str::slug($state));
                                }
                            })
                            ->columnSpan([
                                'default' => 2,
                                'sm' => 2,
                                'md' => 2,
                                'lg' => 2,
                                'xl' => 1,
                                '2xl' => 1,
                            ]),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->unique('dashed__article_authors', 'slug', fn ($record) => $record)
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
                        FileUpload::make('image')
                            ->directory('dashed/article-authors/images')
                            ->name('Afbeelding')
                            ->image()
                            ->columnSpan([
                                'default' => 2,
                                'sm' => 2,
                                'md' => 2,
                                'lg' => 2,
                                'xl' => 2,
                                '2xl' => 2,
                            ]),
                    ]))->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Naam')
                    ->sortable()
                    ->searchable([
                        'name',
                        'slug',
                    ]),
            ])
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
            'index' => ListAuthor::route('/'),
            'create' => CreateAuthor::route('/create'),
            'edit' => EditAuthor::route('/{record}/edit'),
        ];
    }
}
