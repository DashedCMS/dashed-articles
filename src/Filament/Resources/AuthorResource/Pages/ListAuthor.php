<?php

namespace Dashed\DashedArticles\Filament\Resources\AuthorResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use Dashed\DashedArticles\Filament\Resources\AuthorResource;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

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
