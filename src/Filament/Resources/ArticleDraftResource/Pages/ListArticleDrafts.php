<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleDraftResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Dashed\DashedArticles\Filament\Resources\ArticleDraftResource;

class ListArticleDrafts extends ListRecords
{
    protected static string $resource = ArticleDraftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
