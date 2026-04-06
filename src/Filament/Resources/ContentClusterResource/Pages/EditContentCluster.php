<?php

namespace Dashed\DashedArticles\Filament\Resources\ContentClusterResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use Dashed\DashedArticles\Jobs\GenerateArticleJob;
use Dashed\DashedArticles\Models\ArticleDraft;
use Dashed\DashedArticles\Filament\Resources\ArticleDraftResource;
use Dashed\DashedArticles\Filament\Resources\ContentClusterResource;

class EditContentCluster extends EditRecord
{
    protected static string $resource = ContentClusterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('write_article')
                ->label('Artikel schrijven')
                ->icon('heroicon-o-pencil-square')
                ->color('success')
                ->visible(fn () => class_exists(\Dashed\DashedCore\Classes\ClaudeHelper::class) && \Dashed\DashedCore\Classes\ClaudeHelper::isConnected())
                ->schema([
                    \Filament\Forms\Components\TextInput::make('keyword')
                        ->label('Zoekwoord / onderwerp')
                        ->default(fn () => $this->record->keywords()->where('type', 'primary')->value('keyword')
                            ?? $this->record->keywords()->first()?->keyword
                            ?? $this->record->name)
                        ->required(),
                    \Filament\Forms\Components\Select::make('locale')
                        ->label('Taal')
                        ->options(\Dashed\DashedCore\Classes\Locales::getLocalesArray())
                        ->default(fn () => $this->record->keywordResearch?->locale ?? \Dashed\DashedCore\Classes\Locales::getFirstLocale()['id'] ?? 'nl')
                        ->required(),
                    \Filament\Forms\Components\Textarea::make('instruction')
                        ->label('Extra instructie (optioneel)')
                        ->placeholder('Bijv: focus op beginners, gebruik een informele toon')
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->schemaColumns(2)
                ->action(function (array $data): void {
                    $draft = ArticleDraft::create([
                        'content_cluster_id' => $this->record->id,
                        'keyword' => $data['keyword'],
                        'locale' => $data['locale'],
                        'instruction' => $data['instruction'] ?? null,
                        'status' => 'pending',
                    ]);

                    GenerateArticleJob::dispatch($draft);

                    // Mark cluster as in progress
                    $this->record->update(['status' => 'in_progress']);

                    Notification::make()
                        ->title('Artikel wordt geschreven')
                        ->body('Je wordt doorgestuurd naar de voortgangspagina.')
                        ->success()
                        ->send();

                    redirect(ArticleDraftResource::getUrl('view', ['record' => $draft]));
                }),

            DeleteAction::make(),
        ];
    }
}
