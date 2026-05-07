<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use Dashed\DashedCore\Filament\Concerns\HasNestableSortingAction;
use Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class ListArticleCategories extends ListRecords
{
    use HasNestableSortingAction;
    use Translatable;

    protected static string $resource = ArticleCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return array_values(array_filter([
            $this->getNestableSortingHeaderAction(),
            LocaleSwitcher::make(),
            CreateAction::make(),
        ]));
    }
}
