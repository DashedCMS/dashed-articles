<?php

namespace Dashed\DashedArticles\Filament\Resources\KeywordResearchResource\RelationManagers;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Collection;
use Filament\Resources\RelationManagers\RelationManager;

class KeywordsRelationManager extends RelationManager
{
    protected static string $relationship = 'keywords';

    protected static ?string $title = 'Zoekwoorden';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('type')
            ->columns([
                TextColumn::make('keyword')
                    ->label('Zoekwoord')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('type_label')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($record) => $record->type_color),
                TextColumn::make('search_intent')
                    ->label('Intentie')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'informational' => 'Informatief',
                        'commercial' => 'Commercieel',
                        'transactional' => 'Transactioneel',
                        'navigational' => 'Navigatie',
                        default => $state,
                    }),
                TextColumn::make('difficulty')
                    ->label('Moeilijkheid')
                    ->badge()
                    ->color(fn ($record) => $record->difficulty_color)
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'easy' => 'Makkelijk',
                        'medium' => 'Gemiddeld',
                        'hard' => 'Moeilijk',
                        default => $state,
                    }),
                TextColumn::make('volume_indication')
                    ->label('Volume')
                    ->badge()
                    ->color(fn ($record) => $record->volume_color)
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'low' => 'Laag',
                        'medium' => 'Gemiddeld',
                        'high' => 'Hoog',
                        default => $state,
                    }),
                TextColumn::make('status_label')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record) => $record->status_color),
                TextColumn::make('notes')
                    ->label('Notities')
                    ->limit(60)
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'primary' => 'Primair',
                        'secondary' => 'Secundair',
                        'long_tail' => 'Long-tail',
                        'lsi' => 'LSI / semantisch',
                        'question' => 'Vraag',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'new' => 'Nieuw',
                        'approved' => 'Goedgekeurd',
                        'blacklisted' => 'Geblacklist',
                    ]),
                SelectFilter::make('difficulty')
                    ->label('Moeilijkheid')
                    ->options([
                        'easy' => 'Makkelijk',
                        'medium' => 'Gemiddeld',
                        'hard' => 'Moeilijk',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Goedkeuren')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== 'approved')
                    ->action(fn ($record) => $record->update(['status' => 'approved'])),
                Action::make('blacklist')
                    ->label('Blacklisten')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status !== 'blacklisted')
                    ->action(fn ($record) => $record->update(['status' => 'blacklisted'])),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve_selected')
                        ->label('Goedkeuren')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'approved']))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('blacklist_selected')
                        ->label('Blacklisten')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'blacklisted']))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
