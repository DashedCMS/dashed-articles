<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages;

use Illuminate\Support\Str;
use Dashed\DashedCore\Classes\Sites;
use Filament\Actions\LocaleSwitcher;
use Dashed\DashedArticles\Models\Article;
use Filament\Resources\Pages\CreateRecord;
use Dashed\DashedArticles\Filament\Resources\ArticleResource;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateArticle extends CreateRecord
{
    use Translatable;

    protected static string $resource = ArticleResource::class;

    protected function getActions(): array
    {
        return [
          LocaleSwitcher::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        while (Article::where('slug->' . $this->activeLocale, $data['slug'])->count()) {
            $data['slug'] .= Str::random(1);
        }

        $data['site_ids'] = $data['site_ids'] ?? [Sites::getFirstSite()['id']];

        return $data;
    }
}
