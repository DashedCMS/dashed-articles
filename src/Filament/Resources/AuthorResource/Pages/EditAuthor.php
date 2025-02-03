<?php

namespace Dashed\DashedArticles\Filament\Resources\AuthorResource\Pages;

use Dashed\DashedArticles\Models\ArticleAuthor;
use Dashed\DashedCore\Filament\Concerns\HasEditableCMSActions;
use Illuminate\Support\Str;
use Filament\Actions\DeleteAction;
use Filament\Actions\LocaleSwitcher;
use Dashed\DashedArticles\Models\Author;
use Filament\Resources\Pages\EditRecord;
use Dashed\DashedArticles\Filament\Resources\AuthorResource;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;

class EditAuthor extends EditRecord
{
    use HasEditableCMSActions;

    protected static string $resource = AuthorResource::class;

    protected function getHeaderActions(): array
    {
        return self::CMSActions();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        while (ArticleAuthor::where('id', '!=', $this->record->id)->where('slug->'.$this->activeLocale, $data['slug'])->count()) {
            $data['slug'] .= Str::random(1);
        }

        return $data;
    }
}
