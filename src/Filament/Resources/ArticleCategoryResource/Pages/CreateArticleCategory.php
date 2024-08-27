<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Dashed\DashedCore\Filament\Concerns\HasCreatableCMSActions;
use Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource;

class CreateArticleCategory extends CreateRecord
{
    use HasCreatableCMSActions;

    protected static string $resource = ArticleCategoryResource::class;

    protected function getActions(): array
    {
        return self::CMSActions();
    }
}
