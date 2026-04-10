<?php

namespace Dashed\DashedArticles\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Dashed\DashedArticles\Models\Keyword;
use Dashed\DashedCore\Classes\ClaudeHelper;
use Dashed\DashedCore\Models\Customsetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Dashed\DashedArticles\Models\ContentCluster;
use Dashed\DashedArticles\Models\KeywordResearch;
use Dashed\DashedCore\Exceptions\ClaudeRateLimitException;

class RunKeywordResearchJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 10;
    public int $timeout = 300;

    public function backoff(): array
    {
        return [15, 30, 60, 60, 60, 60, 60, 60, 60];
    }

    public function __construct(
        public KeywordResearch $research,
    ) {
    }

    public function failed(\Throwable $exception): void
    {
        $this->research->update([
            'status' => 'failed',
            'progress_message' => null,
            'error_message' => $exception->getMessage(),
        ]);
    }

    public function handle(): void
    {
        $attempt = $this->attempts();
        $progressSuffix = $attempt > 1 ? " (poging {$attempt}/{$this->tries})" : '';
        $siteName = Customsetting::get('site_name') ?: config('app.name');
        $brandContext = ClaudeHelper::getBrandContext();

        $this->research->update(['status' => 'running']);
        $this->research->setProgress("Zoekwoorden analyseren voor \"{$this->research->seed_keyword}\"...{$progressSuffix}");

        try {
            $result = ClaudeHelper::runJsonPrompt(
                $this->buildPrompt($siteName, $brandContext),
                maxTokens: 8000,
            );
        } catch (ClaudeRateLimitException $e) {
            $this->research->setProgress("Rate limit bereikt (poging {$attempt}/{$this->tries}), wordt over ~1 minuut hervat.");
            $this->release(60);

            return;
        } catch (\Throwable $e) {
            $this->research->setProgress("Mislukt, opnieuw proberen... ({$e->getMessage()})");

            throw $e;
        }

        // Store keywords
        $keywordRecords = [];
        foreach ($result['keywords'] ?? [] as $kw) {
            $keywordRecords[] = Keyword::create([
                'keyword_research_id' => $this->research->id,
                'keyword' => $kw['keyword'],
                'type' => $kw['type'] ?? 'secondary',
                'search_intent' => $kw['search_intent'] ?? 'informational',
                'difficulty' => $kw['difficulty'] ?? 'medium',
                'volume_indication' => $kw['volume_indication'] ?? 'medium',
                'notes' => $kw['notes'] ?? null,
                'status' => 'new',
            ]);
        }

        // Store suggested content clusters
        foreach ($result['clusters'] ?? [] as $cluster) {
            $clusterRecord = ContentCluster::create([
                'keyword_research_id' => $this->research->id,
                'name' => $cluster['name'],
                'theme' => $cluster['theme'] ?? null,
                'content_type' => $cluster['content_type'] ?? 'blog',
                'description' => $cluster['description'] ?? null,
                'status' => 'planned',
            ]);

            // Attach keywords by index
            $indices = $cluster['keyword_indices'] ?? [];
            foreach ($indices as $index) {
                if (isset($keywordRecords[$index])) {
                    $clusterRecord->keywords()->attach($keywordRecords[$index]->id);
                }
            }
        }

        $this->research->update([
            'status' => 'done',
            'progress_message' => null,
        ]);
    }

    private function buildPrompt(string $siteName, string $brandContext): string
    {
        $seed = $this->research->seed_keyword;
        $locale = $this->research->locale;

        return <<<PROMPT
        Je bent een SEO-specialist en content strateeg. Doe uitgebreid zoekwoord onderzoek voor het opgegeven seed keyword.

        WEBSITE: {$siteName}
        TAAL: {$locale}
        {$brandContext}
        SEED KEYWORD: {$seed}

        Vind alle relevante zoekwoorden en groepeer ze in content clusters.

        ZOEKWOORD TYPES:
        - primary: het hoofdzoekwoord (1-2 stuks)
        - secondary: direct gerelateerde zoekwoorden met hoog zoekvolume (5-10 stuks)
        - long_tail: specifieke, langere zoekwoordvarianten (10-20 stuks)
        - lsi: semantisch gerelateerde termen en synoniemen (5-10 stuks)
        - question: vragen die mensen stellen (5-10 stuks, begin met "wat", "hoe", "waarom", etc.)

        ZOEKINTENTIE:
        - informational: mensen zoeken informatie
        - commercial: mensen vergelijken producten/diensten
        - transactional: mensen willen kopen
        - navigational: mensen zoeken een specifieke website/pagina

        MOEILIJKHEIDSGRAAD (difficulty):
        - easy: weinig concurrentie, goed te ranken
        - medium: gemiddelde concurrentie
        - hard: veel concurrentie, moeilijk te ranken

        ZOEKVOLUME INDICATIE (volume_indication):
        - low: < 100 zoekopdrachten/maand
        - medium: 100-1000 zoekopdrachten/maand
        - high: > 1000 zoekopdrachten/maand

        CONTENT CLUSTER TYPES (content_type):
        - blog: informatief blog artikel
        - landing_page: conversiegerichte landingspagina
        - category: categoriepagina voor een productgroep of thema
        - faq: veelgestelde vragen pagina
        - product: productpagina
        - other: anders

        Stel ook content clusters voor. Een cluster groepeert keywords die samen één pagina/artikel kunnen ondersteunen.

        BELANGRIJK: Houd de "notes" velden zeer kort (max 8 woorden). Houd "description" in clusters ook kort (max 15 woorden). Retourneer UITSLUITEND geldig JSON zonder markdown code blocks:
        {
          "keywords": [
            {
              "keyword": "...",
              "type": "primary|secondary|long_tail|lsi|question",
              "search_intent": "informational|commercial|transactional|navigational",
              "difficulty": "easy|medium|hard",
              "volume_indication": "low|medium|high",
              "notes": "max 8 woorden waarom relevant"
            }
          ],
          "clusters": [
            {
              "name": "Naam van de cluster",
              "theme": "Overkoepelend thema",
              "content_type": "blog|landing_page|category|faq|product|other",
              "description": "Wat dit stuk content moet bereiken en behandelen",
              "keyword_indices": [0, 1, 2]
            }
          ]
        }

        De keyword_indices verwijzen naar de index (0-based) van keywords in de keywords array die bij deze cluster horen.
        PROMPT;
    }
}
