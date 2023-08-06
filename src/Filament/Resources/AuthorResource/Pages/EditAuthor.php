<?php

namespace Dashed\DashedArticles\Filament\Resources\AuthorResource\Pages;

use Filament\Pages\Actions\ButtonAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;
use Illuminate\Support\Str;
use Dashed\DashedArticles\Filament\Resources\AuthorResource;
use Dashed\DashedArticles\Models\Author;

class EditAuthor extends EditRecord
{
    use Translatable;

    protected static string $resource = AuthorResource::class;

    //    protected function getActions(): array
    //    {
    //        return array_merge(parent::getActions(), [
    //            ButtonAction::make('view_article')
    //                ->label('Bekijk auteur')
    //                ->url($this->record->getUrl())
    //                ->openUrlInNewTab(),
    //            $this->getActiveFormLocaleSelectAction(),
    //        ]);
    //    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        while (Author::where('id', '!=', $this->record->id)->where('slug->' . $this->activeFormLocale, $data['slug'])->count()) {
            $data['slug'] .= Str::random(1);
        }

        return $data;
    }
}
