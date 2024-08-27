<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages;

use Dashed\DashedArticles\Filament\Resources\ArticleResource;
use Dashed\DashedCore\Filament\Concerns\HasEditableCMSActions;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    use HasEditableCMSActions;

    protected static string $resource = ArticleResource::class;

    protected function getActions(): array
    {
        return self::CMSActions();
    }
}
