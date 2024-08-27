<?php

namespace Dashed\DashedArticles\Filament\Resources\AuthorResource\Pages;

use Dashed\DashedArticles\Filament\Resources\AuthorResource;
use Dashed\DashedArticles\Models\Article;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;
use Illuminate\Support\Str;

class CreateAuthor extends CreateRecord
{
    use Translatable;

    protected static string $resource = AuthorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        while (Article::where('slug->'.$this->activeLocale, $data['slug'])->count()) {
            $data['slug'] .= Str::random(1);
        }

        return $data;
    }
}
