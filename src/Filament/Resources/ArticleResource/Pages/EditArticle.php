<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Dashed\DashedArticles\Filament\Resources\ArticleResource;
use Dashed\DashedCore\Filament\Concerns\HasEditableCMSActions;

class EditArticle extends EditRecord
{
    use HasEditableCMSActions;

    protected static string $resource = ArticleResource::class;

    protected function getActions(): array
    {
        return self::CMSActions();
    }
}
