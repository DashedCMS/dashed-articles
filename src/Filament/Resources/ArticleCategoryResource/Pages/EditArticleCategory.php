<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource\Pages;

use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;
use Illuminate\Support\Str;
use Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource;
use Dashed\DashedArticles\Models\ArticleCategory;
use Dashed\DashedCore\Classes\Sites;
use Dashed\DashedCore\Models\Redirect;

class EditArticleCategory extends EditRecord
{
    use Translatable;

    protected static string $resource = ArticleCategoryResource::class;

    protected function getActions(): array
    {
        return array_merge(parent::getActions(), [
            Action::make('view_article_category')
                ->button()
                ->label('Bekijk artikel categorie')
                ->url($this->record->getUrl())
                ->openUrlInNewTab(),
            $this->getActiveFormLocaleSelectAction(),
        ]);
    }

    protected function beforeSave(): void
    {
        Redirect::handleSlugChange($this->record->slug, $this->data['slug']);
    }

    //    public function afterFill(): void
    //    {
    //        foreach ($this->data['blocks'][$this->activeFormLocale] ?? [] as $key => $value) {
    //            if ($value) {
    //                if (Str::contains($value, 'dashed/')) {
    //                    $this->data['blocks_' . $key] = [Str::uuid()->toString() => $value];
    //                } else {
    //                    $this->data['blocks_' . $key] = $value;
    //                }
    //            }
    //        }
    //
    //        $this->data['blocks'] = null;
    //    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        while (ArticleCategory::where('id', '!=', $this->record->id)->where('slug->' . $this->activeFormLocale, $data['slug'])->count()) {
            $data['slug'] .= Str::random(1);
        }

        $data['site_ids'] = $data['site_ids'] ?? [Sites::getFirstSite()['id']];

        Redirect::handleSlugChange($this->record->getTranslation('slug', $this->activeFormLocale), $data['slug']);

        return $data;
    }
}
