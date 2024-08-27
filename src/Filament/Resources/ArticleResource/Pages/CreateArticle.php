<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages;

use Dashed\DashedArticles\Filament\Resources\ArticleResource;
use Dashed\DashedCore\Filament\Concerns\HasCreatableCMSActions;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    use HasCreatableCMSActions;

    protected static string $resource = ArticleResource::class;

    protected function getActions(): array
    {
        return self::CMSActions();
    }
}
