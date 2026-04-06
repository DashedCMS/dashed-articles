<?php

namespace Dashed\DashedArticles\Filament\Resources;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Dashed\DashedArticles\Models\ContentCluster;
use Dashed\DashedArticles\Models\Keyword;
use Dashed\DashedArticles\Filament\Resources\ContentClusterResource\Pages;

class ContentClusterResource extends Resource
{
    protected static ?string $model = ContentCluster::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-group';
    protected static string|\UnitEnum|null $navigationGroup = 'SEO';
    protected static ?string $navigationLabel = 'Content clusters';
    protected static ?string $modelLabel = 'Content cluster';
    protected static ?string $pluralModelLabel = 'Content clusters';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Cluster details')
                ->schema([
                    TextInput::make('name')
                        ->label('Naam')
                        ->required()
                        ->maxLength(255),
                    Select::make('content_type')
                        ->label('Type pagina')
                        ->options([
                            'blog' => 'Blog',
                            'landing_page' => 'Landingspagina',
                            'category' => 'Categoriepagina',
                            'faq' => 'FAQ pagina',
                            'product' => 'Productpagina',
                            'other' => 'Anders',
                        ])
                        ->default('blog')
                        ->required(),
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'planned' => 'Gepland',
                            'in_progress' => 'In uitvoering',
                            'done' => 'Klaar',
                        ])
                        ->default('planned')
                        ->required(),
                    TextInput::make('theme')
                        ->label('Overkoepelend thema')
                        ->placeholder('Bijv: duurzame energie, financiële planning')
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label('Beschrijving')
                        ->placeholder('Wat moet dit stuk content bereiken? Wie is de doelgroep?')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('Zoekwoorden')
                ->schema([
                    Select::make('keywords')
                        ->label('Gekoppelde zoekwoorden')
                        ->relationship('keywords', 'keyword')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('content_type_label')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($record) => $record->content_type_color),
                TextColumn::make('status_label')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record) => $record->status_color),
                TextColumn::make('keywords_count')
                    ->label('Zoekwoorden')
                    ->counts('keywords'),
                TextColumn::make('article_drafts_count')
                    ->label('Artikelen')
                    ->counts('articleDrafts'),
                TextColumn::make('keywordResearch.seed_keyword')
                    ->label('Onderzoek')
                    ->placeholder('-')
                    ->limit(30),
                TextColumn::make('created_at')
                    ->label('Aangemaakt')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('content_type')
                    ->label('Type')
                    ->options([
                        'blog' => 'Blog',
                        'landing_page' => 'Landingspagina',
                        'category' => 'Categoriepagina',
                        'faq' => 'FAQ pagina',
                        'product' => 'Productpagina',
                        'other' => 'Anders',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'planned' => 'Gepland',
                        'in_progress' => 'In uitvoering',
                        'done' => 'Klaar',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContentClusters::route('/'),
            'create' => Pages\EditContentCluster::route('/create'),
            'edit' => Pages\EditContentCluster::route('/{record}/edit'),
        ];
    }
}
