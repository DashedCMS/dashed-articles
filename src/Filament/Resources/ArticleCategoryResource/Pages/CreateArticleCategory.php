<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;
use Illuminate\Support\Str;
use Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource;
use Dashed\DashedArticles\Models\ArticleCategory;
use Dashed\DashedCore\Classes\Sites;

class CreateArticleCategory extends CreateRecord
{
    use Translatable;

    protected static string $resource = ArticleCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        while (ArticleCategory::where('slug->' . $this->activeFormLocale, $data['slug'])->count()) {
            $data['slug'] .= Str::random(1);
        }

        $data['site_ids'] = $data['site_ids'] ?? [Sites::getFirstSite()['id']];

        return $data;
    }
}
