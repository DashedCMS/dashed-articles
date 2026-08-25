<?php

namespace Dashed\DashedArticles\Ai\NewsletterTools;

use Dashed\DashedArticles\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Dashed\DashedNewsletter\Ai\Contracts\SearchTool;
use Dashed\DashedArticles\Mail\EmailBlocks\ArticlesBlock;

/**
 * Artikelen zoeken voor fase 1 van de nieuwsbriefgenerator.
 *
 * ArticlesBlock::visibleArticles() is dezelfde bron die het artikelblok
 * gebruikt bij het renderen: openbaar, op de juiste site, en binnen het
 * publicatievenster. Wat het model mag zien is dus per definitie wat er in de
 * mail terecht mag komen.
 *
 * De grens op het aantal resultaten wordt hier zelf uit de config gelezen en
 * niet uit dashed-ecommerce-core geleend: dit pakket hangt niet aan de webshop
 * en dat hoort zo te blijven.
 */
class ArticleSearchTool implements SearchTool
{
    public function name(): string
    {
        return 'searchArticles';
    }

    public function description(): string
    {
        return 'Zoek gepubliceerde artikelen van deze website op trefwoord. Geeft titel, samenvatting en link terug. Gebruik dit om te kiezen welke artikelen in de nieuwsbrief komen.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string', 'description' => 'Zoekterm, bijvoorbeeld een onderwerp.'],
                'limit' => ['type' => 'integer', 'description' => 'Hoeveel resultaten je wilt.'],
            ],
            'required' => ['query'],
        ];
    }

    public function handle(array $input, ?string $siteId): array
    {
        $zoekterm = trim((string) ($input['query'] ?? ''));

        if ($zoekterm === '') {
            return ['results' => []];
        }

        $max = (int) config('dashed-newsletter.ai.max_search_results', 25);
        $limiet = max(1, min((int) ($input['limit'] ?? $max), $max));

        $artikelen = ArticlesBlock::visibleArticles($siteId)
            ->where(function (Builder $query) use ($zoekterm): void {
                $query->where('name', 'like', '%' . $zoekterm . '%')
                    ->orWhere('excerpt', 'like', '%' . $zoekterm . '%');
            })
            ->limit($limiet)
            ->get();

        return [
            'results' => $artikelen->map(fn (Article $artikel): array => [
                'id' => $artikel->id,
                'name' => $artikel->name,
                'excerpt' => $artikel->excerpt,
                'url' => rescue(fn () => $artikel->getUrl(null, false), null, false),
                'image' => rescue(fn () => mediaHelper()->getSingleMedia($artikel->image, 'small')?->url, null, false),
            ])->values()->all(),
        ];
    }
}
