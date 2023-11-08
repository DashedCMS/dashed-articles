<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\LocaleSwitcher;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Concerns\Translatable;
use Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource;

class ListArticleCategories extends ListRecords
{
    use Translatable;

    protected static string $resource = ArticleCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
