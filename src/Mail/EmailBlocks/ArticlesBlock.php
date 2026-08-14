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

        // visibleArticles() erbij: een artikel dat op de site verborgen is of
        // nog een embargodatum heeft, hoort ook niet in een mail te staan. En
        // sortBy houdt de volgorde aan die de redacteur koos, want whereIn
        // geeft die niet terug.
        $articles = self::visibleArticles($context['siteId'] ?? null)
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Article $article): int => array_search($article->id, $ids, true))
            ->values();

        return self::renderArticles($articles, (int) ($blockData['columns'] ?? 2), $context);
    }

    /**
     * Zichtbare artikelen voor de mail: openbaar, binnen de embargodatums, en
     * op de juiste site als die bekend is.
     *
     * Volgt de front-end (IsVisitable::scopePublicShowable(), zie
     * Article::getResults()/getAllResults()): ook start_date en end_date
     * afdwingen, niet alleen isPublic(). Zonder die datums wordt een artikel
     * met een toekomstige publicatiedatum alsnog gemaild, met een leesknop
     * naar een pagina die de site nog verbergt.
     *
     * Geen thisSite()->publicShowable() zoals de front-end: publicShowable()
     * roept zelf óók thisSite() aan, zonder site-parameter, en valt dan terug
     * op Sites::getActive(). In een queue-job (waar deze mail vandaan komt)
     * is er geen HTTP-request om de actieve site uit af te leiden, dus dat
     * zou de eerst geconfigureerde site pakken in plaats van de site van de
     * campagne, en met twee thisSite()-voorwaarden (één correct, één
     * toevallig) blijft er op een echte multisite-installatie niets meer
     * over. whereJsonContains() met een expliciet siteId omzeilt dat. Ook
     * geen admin-uitzondering zoals scopePublicShowable() die kent: een
     * nieuwsbrief gaat naar echte ontvangers, niet naar een ingelogde
     * beheerder die aan het bladeren is.
     */
    public static function visibleArticles(?string $siteId): \Illuminate\Database\Eloquent\Builder
    {
        $query = Article::isPublic();

        if ($siteId) {
            $query->whereJsonContains('site_ids', $siteId);
        }

        return $query
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            });
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
