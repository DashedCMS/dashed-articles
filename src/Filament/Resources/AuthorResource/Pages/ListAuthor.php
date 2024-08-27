<?php

namespace Dashed\DashedArticles\Filament\Resources\AuthorResource\Pages;

use Dashed\DashedArticles\Filament\Resources\AuthorResource;
use Filament\Actions\CreateAction;
use Filament\Actions\LocaleSwitcher;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Concerns\Translatable;

class ListAuthor extends ListRecords
{
    use Translatable;

    protected static string $resource = AuthorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
