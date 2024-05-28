<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource\Pages;

use Dashed\DashedCore\Filament\Concerns\HasCreatableCMSActions;
use Illuminate\Support\Str;
use Dashed\DashedCore\Classes\Sites;
use Filament\Actions\LocaleSwitcher;
use Filament\Resources\Pages\CreateRecord;
use Dashed\DashedArticles\Models\ArticleCategory;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;
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
