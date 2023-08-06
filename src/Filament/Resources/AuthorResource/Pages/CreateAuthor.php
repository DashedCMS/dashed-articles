<?php

namespace Dashed\DashedArticles\Filament\Resources\AuthorResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;
use Illuminate\Support\Str;
use Dashed\DashedArticles\Filament\Resources\AuthorResource;
use Dashed\DashedArticles\Models\Article;

class CreateAuthor extends CreateRecord
{
    use Translatable;

    protected static string $resource = AuthorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        while (Article::where('slug->' . $this->activeFormLocale, $data['slug'])->count()) {
            $data['slug'] .= Str::random(1);
        }

        return $data;
    }
}
