<?php

namespace Dashed\DashedArticles\Mail\EmailBlocks;

use Filament\Forms\Components\Select;
use Dashed\DashedArticles\Models\Article;
use Filament\Forms\Components\Builder\Block;
use Dashed\DashedCore\Mail\EmailBlocks\EmailBlock;

class ArticlesBlock extends EmailBlock
{
    public static function key(): string
    {
        return 'articles';
    }

    public static function label(): string
    {
        return __('Artikelen');
    }

    public static function contexts(): array
    {
        return [self::CONTEXT_NEWSLETTER];
    }

    public static function filamentBlock(): Block
    {
        return Block::make(self::key())
            ->label(self::label())
            ->icon('heroicon-o-newspaper')
            ->schema([
                Select::make('article_ids')
                    ->label(__('Artikelen'))
                    ->multiple()
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => Article::isPublic()
                        ->where('name', 'like', "%{$search}%")
                        ->limit(20)
                        ->pluck('name', 'id')
                        ->all())
                    ->getOptionLabelsUsing(fn (array $values): array => Article::whereIn('id', $values)->pluck('name', 'id')->all())
                    ->required(),
                Select::make('columns')
                    ->label(__('Aantal kolommen'))
                    ->options([1 => '1', 2 => '2', 3 => '3'])
                    ->default(2),
            ]);
    }

    public static function render(array $blockData, array $context): string
    {
        $ids = array_filter((array) ($blockData['article_ids'] ?? []));

        if ($ids === []) {
            return '';
        }

        // isPublic() erbij: een artikel dat op de site verborgen is, hoort
        // ook niet in een mail te staan. En sortBy houdt de volgorde aan die
        // de redacteur koos, want whereIn geeft die niet terug.
        $articles = Article::isPublic()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Article $article): int => array_search($article->id, $ids, true))
            ->values();

        return self::renderArticles($articles, (int) ($blockData['columns'] ?? 2), $context);
    }

    /**
     * Gedeelde weergave voor ArticlesBlock en LatestArticlesBlock: beide
     * zetten een lijst artikelen om naar hetzelfde raster, met de hand
     * gekozen of automatisch gevuld hoort er hetzelfde uit te zien.
     *
     * @param  \Illuminate\Support\Collection<int, \Dashed\DashedArticles\Models\Article>  $articles
     * @param  array<string, mixed>  $context
     */
    public static function renderArticles($articles, int $columns, array $context): string
    {
        return view('dashed-articles::emails.blocks.articles', [
            'articles' => $articles->map(function (Article $article): array {
                // getSingleMedia() geeft '' terug als er geen afbeelding is, en
                // een object met een url-eigenschap als die er wel is.
                $media = mediaHelper()->getSingleMedia($article->image);

                return [
                    'name' => $article->name,
                    'url' => $article->getUrl(null, false),
                    'image' => is_object($media) ? ($media->url ?? '') : '',
                    'excerpt' => $article->excerpt,
                ];
            })->all(),
            'columns' => max(1, min(3, $columns)),
            'primaryColor' => $context['primaryColor'] ?? '#111827',
            'textColor' => $context['textColor'] ?? '#ffffff',
        ])->render();
    }
}
