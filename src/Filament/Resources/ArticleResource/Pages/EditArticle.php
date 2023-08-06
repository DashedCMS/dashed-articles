<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages;

use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;
use Illuminate\Support\Str;
use Dashed\DashedArticles\Filament\Resources\ArticleResource;
use Dashed\DashedArticles\Models\Article;
use Dashed\DashedCore\Classes\Locales;
use Dashed\DashedCore\Classes\Sites;
use Dashed\DashedCore\Models\Redirect;

class EditArticle extends EditRecord
{
    use Translatable;

    protected static string $resource = ArticleResource::class;

    protected function getActions(): array
    {
        return array_merge(parent::getActions(), [
            Action::make('view_article')
                ->button()
                ->label('Bekijk artikel')
                ->url($this->record->getUrl())
                ->openUrlInNewTab(),
            Action::make('Dupliceer artikel')
                ->action('duplicate')
                ->color('warning'),
            $this->getActiveFormLocaleSelectAction(),
        ]);
    }

    public function duplicate()
    {
        $new = $this->record->replicate();
        foreach (Locales::getLocales() as $locale) {
            $new->setTranslation('slug', $locale['id'], $new->getTranslation('slug', $locale['id']));
            while (Article::where('slug->' . $locale['id'], $new->getTranslation('slug', $locale['id']))->count()) {
                $new->setTranslation('slug', $locale['id'], $new->getTranslation('slug', $locale['id']) . Str::random(1));
            }
        }

        $new->save();

        if ($this->record->customBlocks) {
            $newCustomBlock = $this->record->customBlocks->replicate();
            $newCustomBlock->blockable_id = $new->id;
            $newCustomBlock->save();
        }

        if ($this->record->metaData) {
            $newMetaData = $this->record->metaData->replicate();
            $newMetaData->metadatable_id = $new->id;
            $newMetaData->save();
        }

        return redirect(route('filament.resources.articles.edit', [$new]));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        while (Article::where('id', '!=', $this->record->id)->where('slug->' . $this->activeFormLocale, $data['slug'])->count()) {
            $data['slug'] .= Str::random(1);
        }

        $data['site_ids'] = $data['site_ids'] ?? [Sites::getFirstSite()['id']];

        //        $content = $data['content'];
        //        $data['content'] = $this->record->content;
        //        $data['content'][$this->activeFormLocale] = $content;

        Redirect::handleSlugChange($this->record->getTranslation('slug', $this->activeFormLocale), $data['slug']);

        return $data;
    }
}
