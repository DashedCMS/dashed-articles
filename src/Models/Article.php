<?php

namespace Dashed\DashedArticles\Models;

use Spatie\SchemaOrg\Schema;
use Dashed\DashedPages\Models\Page;
use Illuminate\Support\Facades\App;
use Dashed\DashedCore\Classes\Sites;
use Illuminate\Support\Facades\View;
use Dashed\DashedCore\Classes\Locales;
use Illuminate\Database\Eloquent\Model;
use Dashed\DashedCore\Models\Customsetting;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dashed\DashedCore\Models\Concerns\IsVisitable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Dashed\DashedCore\Models\Concerns\HasCustomBlocks;
use Dashed\LaravelLocalization\Facades\LaravelLocalization;

class Article extends Model
{
    use HasCustomBlocks;
    use IsVisitable;
    use SoftDeletes;

    protected $table = 'dashed__articles';

    public $translatable = [
        'name',
        'slug',
        'content',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'site_ids' => 'array',
        'content' => 'array',
    ];

    protected $appends = [
        'status',
    ];

    protected $with = [
        'author',
    ];

    public function getNextArticle()
    {
        if ($this->category) {
            return $this->category->articles()->thisSite()->publicShowable()->where('id', '>', $this->id)->orderBy('id', 'ASC')->first();
        } else {
            return Article::thisSite()->publicShowable()->where('id', '>', $this->id)->orderBy('id', 'ASC')->first();
        }
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class);
    }

    public function getReadingTimeMinutesAttribute()
    {
        $amount = floor(str_word_count(strip_tags($this->getRawOriginal('content') . json_encode($this->contentBlocks))) / 200);

        return $amount > 0 ? $amount : 1;
    }

    public static function resolveRoute($parameters = [])
    {
        $slug = $parameters['slug'] ?? '';
        if (empty($slug)) {
            return;
        }

        $slugComponents = explode('/', $slug);
        $lastSlugPart = $slugComponents[array_key_last($slugComponents)] ?? null;
        $secondLastSlugPart = $slugComponents[count($slugComponents) - 2] ?? null;

        $overviewPage = self::getOverviewPage();
        $article = self::resolveArticle($lastSlugPart, $slugComponents);

        if (! $article) {
            return;
        }

        if (! self::isValidSlugStructure($article, $overviewPage, $slugComponents, $secondLastSlugPart)) {
            return;
        }

        if ($overviewPage) {

            $page = self::getPageIfExists($overviewPage, $slugComponents[0]);
            if (! $page) {
                return;
            }
        }

        return self::renderArticleView($article, $page ?? null);
    }

    private static function resolveArticle($slug, $slugComponents)
    {
        return Article::publicShowable()
            ->where('slug->' . App::getLocale(), $slug)
            ->first();
    }

    private static function isValidSlugStructure($article, $overviewPage, $slugComponents, $secondLastSlugPart)
    {
        $useCategoryInUrl = Customsetting::get('article_use_category_in_url', null, false);
        $hasOverviewPage = $overviewPage && $overviewPage->id;

        return (! $useCategoryInUrl && count($slugComponents) === ($overviewPage ? 2 : 1))
            || (! $article->category && count($slugComponents) === ($overviewPage ? 2 : 1))
            || ($useCategoryInUrl && $article->category && $article->category->slug === $secondLastSlugPart && count($slugComponents) === ($overviewPage ? 3 : 2));
    }

    private static function getPageIfExists($overviewPage, $firstSlugPart)
    {
        return Page::publicShowable()
            ->isNotHome()
            ->where('slug->' . App::getLocale(), $firstSlugPart)
            ->where('id', $overviewPage->id)
            ->first();
    }

    private static function renderArticleView($article, $page)
    {
        if (! View::exists(env('SITE_THEME', 'dashed') . '.articles.show')) {
            return 'pageNotFound';
        }

        self::setSeoMetadata($article);
        self::setAlternateUrls($article);

        View::share('article', $article);
        View::share('model', $article);
        View::share('breadcrumbs', $article->breadcrumbs());
        View::share('page', $page ?: $article);

        return view(env('SITE_THEME', 'dashed') . '.articles.show');
    }

    private static function setSeoMetadata($article)
    {
        $defaultMetadata = [
            'metaTitle' => $article->metadata->title ?? $article->name,
            'metaDescription' => $article->metadata->description ?? '',
            'ogType' => 'article',
            'metaImage' => $article->metadata->image ?? null,
        ];

        foreach ($defaultMetadata as $key => $value) {
            seo()->metaData($key, $value);
        }

        self::setArticleSchema($article);
    }

    private static function setArticleSchema($article)
    {
        $schema = Schema::article()
            ->name(seo()->metaData('metaTitle'))
            ->url(request()->url())
            ->image(seo()->metaData('metaImage'))
            ->description($article->contentBlocks['excerpt'] ?? '')
            ->author(self::resolveAuthor($article))
            ->publisher(self::resolvePublisher($article))
            ->dateCreated($article->created_at)
            ->dateModified($article->updated_at)
            ->datePublished($article->start_date ?: $article->created_at)
            ->inLanguage(LaravelLocalization::getCurrentLocaleName())
            ->thumbnailUrl(mediaHelper()->getSingleMedia(seo()->metaData('metaImage'))->url ?? '')
            ->timeRequired("PT{$article->readingTimeMinutes}M")
            ->wordCount(str_word_count($article->getPlainContent()))
            ->articleBody($article->getPlainContent())
            ->text($article->getPlainContent())
            ->about($article->category ? $article->category->name : '');

        $schemas = seo()->metaData('schemas') ?? [];
        $schemas['article'] = $schema;
        seo()->metaData('schemas', $schemas);
    }

    private static function resolveAuthor($article)
    {
        return $article->author ? $article->author->name : [
            '@type' => 'Organization',
            '@id' => request()->url() . '#organization',
        ];
    }

    private static function resolvePublisher($article)
    {
        return self::resolveAuthor($article);
    }

    private static function setAlternateUrls($article)
    {
        $currentLocale = App::getLocale();
        $alternateUrls = [];

        foreach (Sites::getLocales() as $locale) {
            if ($locale['id'] !== $currentLocale) {
                LaravelLocalization::setLocale($locale['id']);
                App::setLocale($locale['id']);
                $alternateUrls[$locale['id']] = $article->getUrl();
            }
        }

        LaravelLocalization::setLocale($currentLocale);
        App::setLocale($currentLocale);

        seo()->metaData('alternateUrls', $alternateUrls);
    }

    public function breadcrumbs(): array
    {
        $breadcrumbs = [];
        $model = $this;

        $homePage = Page::isHome()->publicShowable()->first();
        if ($homePage) {
            $breadcrumbs[] = [
                'name' => $homePage->name,
                'url' => $homePage->getUrl(),
            ];
        }

        $overviewPage = self::getOverviewPage();
        if ($overviewPage) {
            $breadcrumbs[] = [
                'name' => $overviewPage->name,
                'url' => $overviewPage->getUrl(),
            ];
        }

        if ($this->category) {
            $categoryBreadcrumbs = [];
            $category = $this->category;
            $categoryBreadcrumbs[] = [
                'name' => $category->name,
                'url' => $category->getUrl(),
            ];
            while ($category->parent) {
                $category = $category->parent;
                $categoryBreadcrumbs[] = [
                    'name' => $category->name,
                    'url' => $category->getUrl(),
                ];
            }
            if (count($categoryBreadcrumbs)) {
                $categoryBreadcrumbs = array_reverse($categoryBreadcrumbs);
                $breadcrumbs = array_merge($breadcrumbs, $categoryBreadcrumbs);
            }

        }

        $breadcrumbs[] = [
            'name' => $this->name,
            'url' => $this->getUrl(),
        ];

        return $breadcrumbs;
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ArticleLike::class)
            ->where('like', 1);
    }

    public function dislikes(): HasMany
    {
        return $this->hasMany(ArticleLike::class)
            ->where('like', 0);
    }

    public function getUrl($activeLocale = null, bool $native = true)
    {
        $originalLocale = app()->getLocale();

        if (! $activeLocale) {
            $activeLocale = $originalLocale;
        }

        $url = '';

        if ($overviewPage = self::getOverviewPage()) {
            if (method_exists($this, 'parent') && $this->parent) {
                $url .= "{$this->parent->getUrl($activeLocale)}/";
            } else {
                $url .= "{$overviewPage->getUrl($activeLocale)}/";
            }
        } else {
            $url .= '/';
        }

        if (Customsetting::get('article_use_category_in_url') && $this->category) {
            $url .= "{$this->category->getTranslation('slug', $activeLocale)}/";
        }

        $url .= $this->getTranslation('slug', $activeLocale);

        if (! str($url)->startsWith('/')) {
            $url = '/' . $url;
        }
        if ($activeLocale != Locales::getFirstLocale()['id'] && ! str($url)->startsWith("/{$activeLocale}")) {
            $url = '/' . $activeLocale . $url;
        }

        return $native ? $url : url($url);
    }
}
