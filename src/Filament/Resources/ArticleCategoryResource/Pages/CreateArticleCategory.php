<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource\Pages;

use Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource;
use Dashed\DashedCore\Filament\Concerns\HasCreatableCMSActions;
use Filament\Resources\Pages\CreateRecord;

class CreateArticleCategory extends CreateRecord
{
    use HasCreatableCMSActions;

    protected static string $resource = ArticleCategoryResource::class;

    protected function getActions(): array
    {
        return self::CMSActions();
    }
}
