<?php

namespace Dashed\DashedArticles\Filament\Resources;

use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Dashed\DashedArticles\Models\KeywordResearch;
use Dashed\DashedArticles\Filament\Resources\KeywordResearchResource\Pages;

class KeywordResearchResource extends Resource
{
    protected static ?string $model = KeywordResearch::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';
    protected static string|\UnitEnum|null $navigationGroup = 'SEO';
    protected static ?string $navigationLabel = 'Zoekwoord onderzoek';
    protected static ?string $modelLabel = 'Zoekwoord onderzoek';
    protected static ?string $pluralModelLabel = 'Zoekwoord onderzoeken';
    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('seed_keyword')
                    ->label('Seed keyword')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('locale')
                    ->label('Taal')
                    ->badge(),
                TextColumn::make('status_label')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record) => $record->status_color),
                TextColumn::make('keywords_count')
                    ->label('Zoekwoorden')
                    ->counts('keywords'),
                TextColumn::make('content_clusters_count')
                    ->label('Clusters')
                    ->counts('contentClusters'),
                TextColumn::make('created_at')
                    ->label('Aangemaakt')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKeywordResearches::route('/'),
            'create' => Pages\CreateKeywordResearch::route('/create'),
            'view' => Pages\ViewKeywordResearch::route('/{record}'),
        ];
    }
}
