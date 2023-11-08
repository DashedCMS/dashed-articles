<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource\Pages;

use Illuminate\Support\Str;
use Dashed\DashedCore\Classes\Sites;
use Filament\Actions\LocaleSwitcher;
use Filament\Resources\Pages\CreateRecord;
use Dashed\DashedArticles\Models\ArticleCategory;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;
use Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource;

class CreateArticleCategory extends CreateRecord
{
    use Translatable;

    protected static string $resource = ArticleCategoryResource::class;

    protected function getActions(): array
    {
        return [
            LocaleSwitcher::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        while (ArticleCategory::where('slug->' . $this->activeLocale, $data['slug'])->count()) {
            $data['slug'] .= Str::random(1);
        }

        $data['site_ids'] = $data['site_ids'] ?? [Sites::getFirstSite()['id']];

        return $data;
    }
}
