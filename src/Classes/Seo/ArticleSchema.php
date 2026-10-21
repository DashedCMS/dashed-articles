<?php

namespace Dashed\DashedArticles\Classes\Seo;

use Illuminate\Support\Str;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\BaseType;
use Dashed\DashedCore\Classes\Sites;
use Dashed\DashedArticles\Models\Article;
use Dashed\DashedCore\Classes\Seo\BusinessProfile;

/**
 * The Article JSON-LD of an article page. The publisher is a reference to
 * the Organization that is already on every page (its @id), the author a
 * Person with the @id of the author page, so Google links the three instead
 * of reading three unrelated blobs. Images are absolute URLs, never the
 * media id that used to end up here.
 */
class ArticleSchema
{
    public static function for(Article $article): BaseType
    {
        $locale = app()->getLocale();
        $url = static::absolute((string) $article->getUrl($locale, false));
        $profile = BusinessProfile::for((string) Sites::getActive(), $locale);

        $schema = Schema::article()
            ->setProperty('@id', $url . '#article')
            ->headline(Str::limit(static::plainText($article->name), 110, ''))
            ->url($url)
            ->mainEntityOfPage(Schema::webPage()->setProperty('@id', $url))
            ->datePublished($article->start_date ?: $article->created_at)
            ->dateModified($article->updated_at ?: ($article->start_date ?: $article->created_at))
            ->inLanguage(static::languageCode($locale))
            ->publisher(['@id' => $profile['id']])
            ->author($article->author ? PersonSchema::person($article->author) : ['@id' => $profile['id']]);

        $images = static::imageUrls([$article->metadata?->image, $article->image]);
        if ($images !== []) {
            $schema->image($images);
        }

        $description = static::plainText($article->metadata?->description ?: $article->excerpt);
        if ($description !== '') {
            $schema->description($description);
        }

        if ($article->category) {
            $schema->articleSection(static::plainText($article->category->name));
        }

        $words = str_word_count(static::plainText($article->getPlainContent()));
        if ($words > 0) {
            $schema->wordCount($words);
        }

        if ((int) $article->readingTimeMinutes > 0) {
            $schema->timeRequired('PT' . (int) $article->readingTimeMinutes . 'M');
        }

        return $schema;
    }

    /**
     * BCP 47 code for the locale: the regional code from the localization
     * config with a dash (nl-NL), else the bare locale.
     */
    public static function languageCode(string $locale): string
    {
        $regional = config("laravellocalization.supportedLocales.{$locale}.regional");

        return $regional ? str_replace('_', '-', (string) $regional) : $locale;
    }

    public static function absolute(string $url): string
    {
        return str_starts_with($url, '/') ? rtrim(url('/'), '/') . $url : $url;
    }

    /**
     * Media ids or pasted URLs to absolute image URLs.
     */
    public static function imageUrls(array $values): array
    {
        $urls = [];

        foreach (\Illuminate\Support\Arr::flatten($values) as $value) {
            if (! $value || ! function_exists('mediaHelper')) {
                continue;
            }

            $media = mediaHelper()->getSingleMedia($value, 'original');
            $url = is_object($media) ? (string) ($media->url ?? '') : (string) $media;

            if ($url !== '' && (str_starts_with($url, 'http') || str_starts_with($url, '/'))) {
                $urls[$url] = static::absolute($url);
            }
        }

        return array_values($urls);
    }

    public static function plainText(mixed $value): string
    {
        if (is_array($value)) {
            $value = rescue(fn () => cms()->convertToHtml($value), '', false);
        }

        $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5);

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
