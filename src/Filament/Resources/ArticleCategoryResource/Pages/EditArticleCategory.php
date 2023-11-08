<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource\Pages;

use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Dashed\DashedCore\Classes\Sites;
use Filament\Actions\LocaleSwitcher;
use Dashed\DashedCore\Models\Redirect;
use Filament\Resources\Pages\EditRecord;
use Dashed\DashedArticles\Models\ArticleCategory;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;
use Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource;

class EditArticleCategory extends EditRecord
{
    use Translatable;

    protected static string $resource = ArticleCategoryResource::class;

    protected function getActions(): array
    {
        return [
            LocaleSwitcher::make(),
            Action::make('view_article_category')
                ->button()
                ->label('Bekijk artikel categorie')
                ->url($this->record->getUrl())
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        if ($this->record->slug) {
            Redirect::handleSlugChange($this->record->slug, $this->data[$this->activeLocale]['slug']);
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        while (ArticleCategory::where('id', '!=', $this->record->id)->where('slug->' . $this->activeLocale, $data['slug'])->count()) {
            $data['slug'] .= Str::random(1);
        }

        $data['site_ids'] = $data['site_ids'] ?? [Sites::getFirstSite()['id']];

        Redirect::handleSlugChange($this->record->getTranslation('slug', $this->activeLocale), $data['slug']);

        return $data;
    }
}
