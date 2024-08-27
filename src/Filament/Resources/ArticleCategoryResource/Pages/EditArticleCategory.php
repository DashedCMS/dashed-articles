<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource\Pages;

use Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource;
use Dashed\DashedCore\Filament\Concerns\HasEditableCMSActions;
use Filament\Resources\Pages\EditRecord;

class EditArticleCategory extends EditRecord
{
    use HasEditableCMSActions;

    protected static string $resource = ArticleCategoryResource::class;

    protected function getActions(): array
    {
        return self::CMSActions();
    }
}
