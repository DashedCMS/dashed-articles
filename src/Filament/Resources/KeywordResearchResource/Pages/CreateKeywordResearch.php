<?php

namespace Dashed\DashedArticles\Filament\Resources\KeywordResearchResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Dashed\DashedCore\Classes\Locales;
use Dashed\DashedArticles\Jobs\RunKeywordResearchJob;
use Dashed\DashedArticles\Models\KeywordResearch;
use Dashed\DashedArticles\Filament\Resources\KeywordResearchResource;

class CreateKeywordResearch extends CreateRecord
{
    protected static string $resource = KeywordResearchResource::class;

    protected static ?string $title = 'Nieuw zoekwoord onderzoek';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('seed_keyword')
                ->label('Seed keyword / onderwerp')
                ->placeholder('Bijv: zonnepanelen, hypotheek berekenen, duurzaam tuinieren')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Select::make('locale')
                ->label('Taal')
                ->options(Locales::getLocalesArray())
                ->default(Locales::getFirstLocale()['id'] ?? 'nl')
                ->required(),
        ])->columns(2);
    }

    protected function handleRecordCreation(array $data): KeywordResearch
    {
        $research = KeywordResearch::create([
            'seed_keyword' => $data['seed_keyword'],
            'locale' => $data['locale'],
            'status' => 'pending',
        ]);

        RunKeywordResearchJob::dispatch($research);

        return $research;
    }

    protected function getRedirectUrl(): string
    {
        return KeywordResearchResource::getUrl('view', ['record' => $this->record]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Zoekwoord onderzoek gestart';
    }
}
