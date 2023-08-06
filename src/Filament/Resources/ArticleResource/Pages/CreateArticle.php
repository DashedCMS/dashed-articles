<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;
use Illuminate\Support\Str;
use Dashed\DashedArticles\Filament\Resources\ArticleResource;
use Dashed\DashedArticles\Models\Article;
use Dashed\DashedCore\Classes\Sites;

class CreateArticle extends CreateRecord
{
    use Translatable;

    protected static string $resource = ArticleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        while (Article::where('slug->' . $this->activeFormLocale, $data['slug'])->count()) {
            $data['slug'] .= Str::random(1);
        }

        $data['site_ids'] = $data['site_ids'] ?? [Sites::getFirstSite()['id']];
        //        $content = $data['content'];
        //        $data['content'] = null;
        //        $data['content'][$this->activeFormLocale] = $content;

        return $data;
    }
}
