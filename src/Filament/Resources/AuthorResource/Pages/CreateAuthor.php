<?php

namespace Dashed\DashedArticles\Filament\Resources\AuthorResource\Pages;

use Illuminate\Support\Str;
use Filament\Resources\Pages\CreateRecord;
use Dashed\DashedArticles\Models\ArticleAuthor;
use Dashed\DashedArticles\Filament\Resources\AuthorResource;
use Dashed\DashedCore\Filament\Concerns\HasCreatableCMSActions;

class CreateAuthor extends CreateRecord
{
    use HasCreatableCMSActions;

    protected static string $resource = AuthorResource::class;

    protected function getActions(): array
    {
        return self::CMSActions();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        while (ArticleAuthor::where('slug->'.$this->activeLocale, $data['slug'])->count()) {
            $data['slug'] .= Str::random(1);
        }

        return $data;
    }
}
