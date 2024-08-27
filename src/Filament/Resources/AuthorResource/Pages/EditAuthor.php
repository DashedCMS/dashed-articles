<?php

namespace Dashed\DashedArticles\Filament\Resources\AuthorResource\Pages;

use Dashed\DashedArticles\Filament\Resources\AuthorResource;
use Dashed\DashedArticles\Models\Author;
use Filament\Actions\DeleteAction;
use Filament\Actions\LocaleSwitcher;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;
use Illuminate\Support\Str;

class EditAuthor extends EditRecord
{
    use Translatable;

    protected static string $resource = AuthorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        while (Author::where('id', '!=', $this->record->id)->where('slug->'.$this->activeLocale, $data['slug'])->count()) {
            $data['slug'] .= Str::random(1);
        }

        return $data;
    }
}
