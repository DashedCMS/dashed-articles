<?php

namespace Dashed\DashedArticles\Filament\Resources\AuthorResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Concerns\Translatable;
use Dashed\DashedArticles\Filament\Resources\AuthorResource;

class ListAuthor extends ListRecords
{
    use Translatable;

    protected static string $resource = AuthorResource::class;
}
