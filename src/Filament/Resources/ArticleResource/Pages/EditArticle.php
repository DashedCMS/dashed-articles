<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages;

use Dashed\DashedCore\Filament\Concerns\HasEditableCMSActions;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Dashed\DashedCore\Classes\Sites;
use Filament\Actions\LocaleSwitcher;
use Dashed\DashedCore\Classes\Locales;
use Dashed\DashedCore\Models\Redirect;
use Filament\Resources\Pages\EditRecord;
use Dashed\DashedArticles\Models\Article;
use Dashed\DashedArticles\Filament\Resources\ArticleResource;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;

class EditArticle extends EditRecord
{
    use HasEditableCMSActions;

    protected static string $resource = ArticleResource::class;

    protected function getActions(): array
    {
        return self::CMSActions();
    }
}
