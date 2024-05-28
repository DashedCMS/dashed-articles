<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages;

use Dashed\DashedCore\Filament\Concerns\HasCreatableCMSActions;
use Illuminate\Support\Str;
use Dashed\DashedCore\Classes\Sites;
use Filament\Actions\LocaleSwitcher;
use Dashed\DashedArticles\Models\Article;
use Filament\Resources\Pages\CreateRecord;
use Dashed\DashedArticles\Filament\Resources\ArticleResource;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateArticle extends CreateRecord
{
    use HasCreatableCMSActions;

    protected static string $resource = ArticleResource::class;

    protected function getActions(): array
    {
        return self::CMSActions();
    }
}
