<?php

namespace Dashed\DashedArticles\Filament\Resources\KeywordResearchResource\RelationManagers;

use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\RelationManagers\RelationManager;
use Dashed\DashedArticles\Filament\Resources\ContentClusterResource;

class ContentClustersRelationManager extends RelationManager
{
    protected static string $relationship = 'contentClusters';

    protected static ?string $title = 'Content clusters';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Naam')
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
                TextColumn::make('description')
                    ->label('Beschrijving')
                    ->limit(60)
                    ->placeholder('-'),
            ])
            ->recordUrl(fn ($record) => ContentClusterResource::getUrl('edit', ['record' => $record]))
            ->recordActions([
                DeleteAction::make(),
            ]);
    }
}
