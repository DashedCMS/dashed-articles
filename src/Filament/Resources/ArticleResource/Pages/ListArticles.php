<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Concerns\Translatable;
use Dashed\DashedArticles\Filament\Resources\ArticleResource;

class ListArticles extends ListRecords
{
    use Translatable;

    protected static string $resource = ArticleResource::class;
}
