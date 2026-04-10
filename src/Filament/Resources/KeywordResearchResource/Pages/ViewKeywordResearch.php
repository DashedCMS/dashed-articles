<?php

namespace Dashed\DashedArticles\Filament\Resources\KeywordResearchResource\Pages;

use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Dashed\DashedArticles\Models\ContentCluster;
use Dashed\DashedArticles\Jobs\RunKeywordResearchJob;
use Dashed\DashedArticles\Filament\Resources\KeywordResearchResource;
use Dashed\DashedArticles\Filament\Resources\KeywordResearchResource\RelationManagers\KeywordsRelationManager;
use Dashed\DashedArticles\Filament\Resources\KeywordResearchResource\RelationManagers\ContentClustersRelationManager;

class ViewKeywordResearch extends ViewRecord
{
    protected static string $resource = KeywordResearchResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(
                fn ($record) => in_array($record->status, ['pending', 'running'])
                ? ($record->progress_message ?: 'Zoekwoorden worden geanalyseerd...')
                : 'Status'
            )
                ->schema([
                    TextEntry::make('seed_keyword')
                        ->label('Seed keyword')
                        ->weight('bold'),
                    TextEntry::make('locale')
                        ->label('Taal')
                        ->badge(),
                    TextEntry::make('status_label')
                        ->label('Status')
                        ->badge()
                        ->color(fn ($record) => $record->status_color),
                    TextEntry::make('created_at')
                        ->label('Aangemaakt')
                        ->dateTime('d-m-Y H:i'),
                ])
                ->columns(4)
                ->extraAttributes(
                    fn ($record) => in_array($record->status, ['pending', 'running'])
                    ? ['wire:poll.3s' => 'refreshPolledData']
                    : []
                )
                ->columnSpanFull(),
        ]);
    }

    public function refreshPolledData(): void
    {
        $this->record = $this->record->fresh();
        $this->refreshFormData(['status', 'progress_message', 'error_message']);
    }

    public function getRelationManagers(): array
    {
        if (in_array($this->record->status, ['pending', 'running'])) {
            return [];
        }

        return [
            KeywordsRelationManager::class,
            ContentClustersRelationManager::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_cluster')
                ->label('Nieuwe cluster aanmaken')
                ->icon('heroicon-o-squares-plus')
                ->color('primary')
                ->visible(fn () => $this->record->status === 'done')
                ->schema([
                    TextInput::make('name')
                        ->label('Naam')
                        ->required(),
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
                    \Filament\Forms\Components\Textarea::make('description')
                        ->label('Beschrijving (optioneel)')
                        ->rows(2)
                        ->columnSpanFull(),
                    Select::make('keyword_ids')
                        ->label('Zoekwoorden koppelen')
                        ->options(fn () => $this->record->keywords()
                            ->where('status', 'approved')
                            ->pluck('keyword', 'id'))
                        ->multiple()
                        ->searchable()
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    $cluster = ContentCluster::create([
                        'keyword_research_id' => $this->record->id,
                        'name' => $data['name'],
                        'content_type' => $data['content_type'],
                        'description' => $data['description'] ?? null,
                        'status' => 'planned',
                    ]);

                    if (! empty($data['keyword_ids'])) {
                        $cluster->keywords()->attach($data['keyword_ids']);
                    }

                    Notification::make()->title('Cluster aangemaakt')->success()->send();
                }),

            Action::make('retry')
                ->label('Opnieuw analyseren')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('Dit verwijdert alle gevonden zoekwoorden en clusters en start het onderzoek opnieuw.')
                ->action(function (): void {
                    $this->record->keywords()->delete();
                    $this->record->contentClusters()->each(function ($cluster) {
                        $cluster->keywords()->detach();
                        $cluster->delete();
                    });

                    $this->record->update([
                        'status' => 'pending',
                        'progress_message' => null,
                        'error_message' => null,
                    ]);

                    RunKeywordResearchJob::dispatch($this->record->fresh());

                    Notification::make()->title('Onderzoek opnieuw gestart')->success()->send();
                }),
        ];
    }
}
