<?php

namespace Dashed\DashedArticles\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Dashed\DashedCore\Classes\ClaudeHelper;
use Dashed\DashedCore\Models\Customsetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Dashed\DashedArticles\Models\KeywordResearch;
use Dashed\DashedCore\Exceptions\ClaudeRateLimitException;

class AutoKeywordDiscoveryJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;
    public int $timeout = 120;

    public function __construct(
        public string $locale,
        public int $maxTopics = 10,
    ) {
    }

    public function handle(): void
    {
        $siteName = Customsetting::get('site_name') ?: config('app.name');
        $brandContext = ClaudeHelper::getBrandContext();
        $content = $this->collectWebsiteContent();

        if (empty($content)) {
            return;
        }

        try {
            $result = ClaudeHelper::runJsonPrompt(
                $this->buildPrompt($siteName, $brandContext, $content),
                maxTokens: 2000,
            );
        } catch (ClaudeRateLimitException) {
            $this->release(60);

            return;
        }

        // Skip topics that already have recent keyword research
        $existing = KeywordResearch::where('locale', $this->locale)
            ->where('created_at', '>=', now()->subDays(30))
            ->pluck('seed_keyword')
            ->map(fn ($k) => strtolower($k))
            ->toArray();

        foreach ($result['topics'] ?? [] as $topic) {
            $keyword = $topic['seed_keyword'] ?? null;
            if (! $keyword) {
                continue;
            }

            if (in_array(strtolower($keyword), $existing)) {
                continue;
            }

            $research = KeywordResearch::create([
                'seed_keyword' => $keyword,
                'locale' => $this->locale,
                'status' => 'pending',
            ]);

            RunKeywordResearchJob::dispatch($research)->delay(now()->addSeconds(5));
        }
    }

    private function collectWebsiteContent(): array
    {
        $content = [];

        try {
            foreach (cms()->builder('routeModels') as $routeModel) {
                $class = $routeModel['class'];
                $label = $routeModel['pluralLabel'] ?? $routeModel['label'] ?? class_basename($class);
                $nameField = $routeModel['nameField'] ?? 'name';

                $names = [];
                foreach ($class::limit(50)->get() as $record) {
                    try {
                        $name = method_exists($record, 'getTranslation')
                            ? $record->getTranslation($nameField, $this->locale)
                            : $record->$nameField;

                        if ($name) {
                            $names[] = $name;
                        }
                    } catch (\Throwable) {
                    }
                }

                if (! empty($names)) {
                    $content[$label] = $names;
                }
            }
        } catch (\Throwable) {
        }

        return $content;
    }

    private function buildPrompt(string $siteName, string $brandContext, array $content): string
    {
        $contentLines = '';
        foreach ($content as $type => $names) {
            $contentLines .= "\n{$type}:\n";
            foreach (array_slice($names, 0, 30) as $name) {
                $contentLines .= "  - {$name}\n";
            }
        }

        $max = $this->maxTopics;

        return <<<PROMPT
        Je bent een SEO-specialist. Analyseer de bestaande content van een website en identificeer de belangrijkste onderwerpen waarvoor zoekwoord onderzoek zinvol is.

        WEBSITE: {$siteName}
        TAAL: {$this->locale}
        {$brandContext}

        BESTAANDE WEBSITE CONTENT:
        {$contentLines}

        Identificeer maximaal {$max} onderwerpen/thema's die:
        - Representatief zijn voor waar de website over gaat
        - Goede kandidaten zijn voor uitgebreid zoekwoord onderzoek
        - Voldoende zoekvolume potentieel hebben
        - Divers genoeg zijn (niet te veel overlap)

        Retourneer UITSLUITEND geldig JSON:
        {
          "topics": [
            {
              "seed_keyword": "het zoekwoord om te onderzoeken",
              "rationale": "waarom dit een goed onderwerp is voor zoekwoord onderzoek"
            }
          ]
        }
        PROMPT;
    }
}
