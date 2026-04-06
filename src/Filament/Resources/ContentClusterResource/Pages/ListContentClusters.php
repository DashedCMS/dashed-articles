<?php

namespace Dashed\DashedArticles\Filament\Resources\ContentClusterResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Dashed\DashedArticles\Filament\Resources\ContentClusterResource;

class ListContentClusters extends ListRecords
{
    protected static string $resource = ContentClusterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
