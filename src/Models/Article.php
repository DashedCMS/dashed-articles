<?php

namespace Dashed\DashedArticles\Models;

use Dashed\DashedPages\Models\Page;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Dashed\DashedCore\Classes\Locales;
use Illuminate\Database\Eloquent\Model;
use Dashed\DashedCore\Models\Customsetting;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dashed\DashedCore\Models\Concerns\IsVisitable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Dashed\DashedArticles\Classes\Seo\ArticleSchema;
use Dashed\DashedCore\Models\Concerns\HasSearchIndex;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Dashed\DashedCore\Models\Concerns\HasCustomBlocks;

class Article extends Model
{
    use HasSearchIndex;
    use LogsActivity;
    use HasCustomBlocks;
    use IsVisitable;
    use SoftDeletes;

    protected $table = 'dashed__articles';

    public $translatable = [
        'name',
        'slug',
        'content',
        'excerpt',
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

    public function getNextArticle(bool $useFirstArticleIfIsLast = true): ?Article
    {
        if ($this->category) {
            $article = $this->category->articles()->thisSite()->publicShowable()->where('id', '>', $this->id)->orderBy('id', 'ASC')->first();
            if (! $article && $useFirstArticleIfIsLast) {
                $article = $this->category->articles()->thisSite()->publicShowable()->orderBy('id', 'ASC')->first();
                if ($article->id == $this->id) {
                    $article = null;
                }
            }
        } else {
            $article = Article::thisSite()->publicShowable()->where('id', '>', $this->id)->orderBy('id', 'ASC')->first();
            if (! $article && $useFirstArticleIfIsLast) {
                $article = Article::thisSite()->publicShowable()->orderBy('id', 'ASC')->first();
                if ($article->id == $this->id) {
                    $article = null;
                }
            }
        }

        return $article ?? null;
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(ArticleAuthor::class);
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
            || ($useCategoryInUrl && $article->category && $article->category->slug === $secondLastSlugPart && count($slugComponents) > ($overviewPage ? 2 : 1));
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
        if (! View::exists(config('dashed-core.site_theme', 'dashed') . '.articles.show')) {
            return 'pageNotFound';
        }

        self::setSeoMetadata($article);

        View::share('article', $article);
        View::share('model', $article);
        request()->attributes->set('dashed.current_visitable', $article);
        View::share('breadcrumbs', $article->breadcrumbs());
        View::share('page', $page ?: $article);

        return view(config('dashed-core.site_theme', 'dashed') . '.articles.show');
    }

    private static function setSeoMetadata($article)
    {
        $defaultMetadata = [
            'metaTitle' => $article->metadata->title ?? $article->name,
            'metaDescription' => $article->metadata && $article->metadata->description ? $article->metadata->description : $article->excerpt,
            'ogType' => 'article',
            'metaImage' => $article->metadata && $article->metadata->image ? $article->metadata->image : $article->image,
        ];

        foreach ($defaultMetadata as $key => $value) {
            seo()->metaData($key, $value);
        }
    }

    /**
     * The head reads this through ModelSchemas: the Article JSON-LD with the
     * author as Person and the publisher as a reference to the Organization.
     * The hreflang links come from getUrl() in the head; the alternateUrls
     * that used to be set here were read by nothing.
     */
    public function seoSchemas(): array
    {
        return ['article' => ArticleSchema::for($this)];
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
            $categoriesToAdd = [];
            $parentCategory = $this->category;
            while ($parentCategory) {
                $categoriesToAdd[] = $parentCategory->getTranslation('slug', $activeLocale);
                $parentCategory = $parentCategory->parent;
            }
            $categoriesToAdd = array_reverse($categoriesToAdd);
            foreach ($categoriesToAdd as $categorySlug) {
                $url .= "{$categorySlug}/";
            }
        }

        $url .= $this->getTranslation('slug', $activeLocale);

        if (! str($url)->startsWith('/')) {
            $url = '/' . $url;
        }
        if ($activeLocale != Locales::getFirstLocale()['id'] && ! str($url)->startsWith("/{$activeLocale}")) {
            $url = '/' . $activeLocale . $url;
        }

        //        dd($url);
        return $native ? $url : url($url);
    }

    public static function canHaveParent(): bool
    {
        return false;
    }

    /**
     * Activity-log integration: emits a row per edit so the Filament
     * LastEditedColumn can surface "who changed this when".
     */
    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Latest activity-log entry. Eager-load via
     * `with('latestActivity.causer')` to avoid N+1 on list pages.
     */
    public function latestActivity(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(\Spatie\Activitylog\Models\Activity::class, 'subject')
            ->latestOfMany('created_at');
    }
}
