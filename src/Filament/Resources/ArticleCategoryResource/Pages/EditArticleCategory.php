<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource\Pages;

use Dashed\DashedArticles\Models\Article;
use Dashed\DashedCore\Classes\Locales;
use Dashed\DashedCore\Filament\Concerns\HasEditableCMSActions;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Dashed\DashedCore\Classes\Sites;
use Filament\Actions\LocaleSwitcher;
use Dashed\DashedCore\Models\Redirect;
use Filament\Resources\Pages\EditRecord;
use Dashed\DashedArticles\Models\ArticleCategory;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;
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
