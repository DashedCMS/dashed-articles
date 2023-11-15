<?php

namespace Dashed\DashedArticles\Filament\Resources\ArticleResource\Pages;

use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Dashed\DashedCore\Classes\Sites;
use Filament\Actions\LocaleSwitcher;
use Dashed\DashedCore\Classes\Locales;
use Dashed\DashedCore\Models\Redirect;
use Filament\Resources\Pages\EditRecord;
use Dashed\DashedArticles\Models\Article;
use Dashed\DashedArticles\Filament\Resources\ArticleResource;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;

class EditArticle extends EditRecord
{
    use Translatable;

    protected static string $resource = ArticleResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('view_article')
                ->button()
                ->label('Bekijk artikel')
                ->url($this->record->getUrl())
                ->openUrlInNewTab(),
            Action::make('Dupliceer artikel')
                ->action('duplicate')
                ->color('warning'),
            LocaleSwitcher::make(),
            DeleteAction::make(),
        ];
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

        return redirect(route('filament.dashed.resources.articles.edit', [$new]));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        while (Article::where('id', '!=', $this->record->id)->where('slug->' . $this->activeLocale, $data['slug'])->count()) {
            $data['slug'] .= Str::random(1);
        }

        $data['site_ids'] = $data['site_ids'] ?? [Sites::getFirstSite()['id']];

        //        $content = $data['content'];
        //        $data['content'] = $this->record->content;
        //        $data['content'][$this->activeLocale] = $content;

        Redirect::handleSlugChange($this->record->getTranslation('slug', $this->activeLocale), $data['slug']);

        return $data;
    }

//    protected function mutateFormDataBeforeFill(array $data): array
//    {
////        ray($this->record->customBlocks->getTranslation('blocks', $this->activeLocale));
////        $data['customBlocks'] = $this->record->customBlocks->getTranslation('blocks', $this->activeLocale) ?? [];
////
////        return $data;
//    }
}
