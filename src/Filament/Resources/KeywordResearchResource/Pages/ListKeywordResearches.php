<?php

namespace Dashed\DashedArticles\Filament\Resources\KeywordResearchResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Dashed\DashedCore\Classes\Locales;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Dashed\DashedCore\Classes\ClaudeHelper;
use Dashed\DashedArticles\Jobs\AutoKeywordDiscoveryJob;
use Dashed\DashedArticles\Filament\Resources\KeywordResearchResource;

class ListKeywordResearches extends ListRecords
{
    protected static string $resource = KeywordResearchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('auto_discover')
                ->label('Automatisch analyseren')
                ->icon('heroicon-o-cpu-chip')
                ->color('warning')
                ->visible(fn () => ClaudeHelper::isConnected())
                ->schema([
                    Select::make('locale')
                        ->label('Taal')
                        ->options(Locales::getLocalesArray())
                        ->default(Locales::getFirstLocale()['id'] ?? 'nl')
                        ->required(),
                    Select::make('max_topics')
                        ->label('Max. aantal onderwerpen')
                        ->options([
                            5 => '5',
                            10 => '10',
                            15 => '15',
                            20 => '20',
                        ])
                        ->default(10)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    AutoKeywordDiscoveryJob::dispatch(
                        locale: $data['locale'],
                        maxTopics: (int) $data['max_topics'],
                    );

                    Notification::make()
                        ->title('Analyse gestart')
                        ->body('De website content wordt geanalyseerd. De zoekwoord onderzoeken verschijnen vanzelf in dit overzicht.')
                        ->success()
                        ->send();
                }),

            CreateAction::make()
                ->label('Handmatig toevoegen'),
        ];
    }
}
