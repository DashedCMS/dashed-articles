<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Dashed\DashedCore\Filament\Concerns\HasEditableCMSActions;
use Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource;

class EditArticleCategory extends EditRecord
{
    use HasEditableCMSActions;

    protected static string $resource = ArticleCategoryResource::class;

    protected function getActions(): array
    {
        return self::CMSActions();
    }
}
