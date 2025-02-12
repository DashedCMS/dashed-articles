<?php

namespace Dashed\DashedArticles\Models;

use Dashed\DashedPages\Models\Page;
use Illuminate\Support\Facades\App;
use Dashed\DashedCore\Classes\Sites;
use Illuminate\Support\Facades\View;
use Dashed\DashedCore\Classes\Locales;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Dashed\DashedCore\Models\Concerns\IsVisitable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Dashed\DashedCore\Models\Concerns\HasCustomBlocks;
use Dashed\LaravelLocalization\Facades\LaravelLocalization;

class ArticleCategory extends Model
{
    use HasCustomBlocks;
    use HasTranslations;
    use IsVisitable;

    protected $table = 'dashed__article_categories';

    public $translatable = [
        'name',
        'slug',
        'content',
    ];

    protected $casts = [
        'site_ids' => 'array',
    ];

    public $with = [
        'parent',
    ];

    public function articles()
    {
        return $this->hasMany(Article::class, 'category_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class);
    }

    public function childs(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id')
            ->orderBy('order');
    }

    public static function resolveRoute($parameters = [])
    {
        $slug = $parameters['slug'] ?? '';
        $slugComponents = explode('/', $slug);
        $locale = App::getLocale();

        $overviewPage = self::getOverviewPage();
        $page = null;

        if ($overviewPage) {
            $page = Page::publicShowable()
                ->isNotHome()
                ->where("slug->$locale", str($slugComponents[0])->replace('/', ''))
                ->where('id', $overviewPage->id)
                ->first();

            if (!$page) {
                return;
            }
            array_shift($slugComponents);
        }

        if (!$slug) {
            return;
        }

        $articleCategory = self::findArticleCategory($slugComponents, $locale);
        if (!$articleCategory) {
            return;
        }

        $viewName = $slugComponents ? 'show' : 'show-overview';
        $viewPath = env('SITE_THEME', 'dashed') . ".article-categories.$viewName";

        if (!View::exists($viewPath)) {
            return 'pageNotFound';
        }

        $model = $slugComponents ? $articleCategory : $page;
        self::setSeoMetadata($model);
        self::setAlternateUrls($model);
        self::shareViewData($model, $articleCategory, $page);

        return view($viewPath);
    }

    private static function findArticleCategory($slugComponents, $locale)
    {
        $parentId = null;
        foreach ($slugComponents as $slugPart) {
            $articleCategory = ArticleCategory::publicShowable()
                ->where("slug->$locale", $slugPart)
                ->where('parent_id', $parentId)
                ->first();

            if (!$articleCategory) {
                return null;
            }
            $parentId = $articleCategory->id;
        }
        return $articleCategory;
    }

    private static function setSeoMetadata($model)
    {
        seo()->metaData('metaTitle', $model->metadata->title ?? $model->name);
        seo()->metaData('metaDescription', $model->metadata->description ?? '');
        seo()->metaData('ogType', 'article');
        if ($model->metadata && $model->metadata->image) {
            seo()->metaData('metaImage', $model->metadata->image);
        }
    }

    private static function setAlternateUrls($model)
    {
        $correctLocale = App::getLocale();
        $alternateUrls = collect(Sites::getLocales())
            ->reject(fn($locale) => $locale['id'] == $correctLocale)
            ->mapWithKeys(function ($locale) use ($model) {
                LaravelLocalization::setLocale($locale['id']);
                App::setLocale($locale['id']);
                return [$locale['id'] => $model->getUrl()];
            });

        LaravelLocalization::setLocale($correctLocale);
        App::setLocale($correctLocale);
        seo()->metaData('alternateUrls', $alternateUrls);
    }

    private static function shareViewData($model, $articleCategory, $page)
    {
        View::share([
            'breadcrumbs' => $model->breadcrumbs(),
            'model' => $model,
            'page' => $page,
        ]);

        if ($articleCategory) {
            View::share([
                'articleCategory' => $articleCategory,
                'articles' => $articleCategory->articles()->paginate(12),
            ]);
        } else {
            View::share('categories', ArticleCategory::publicShowable()->paginate(12));
        }
    }

    public function allArticleIds(): array
    {
        $articleIds = [];

        foreach ($this->childs as $child) {
            $articleIds = array_merge($articleIds, $child->allArticleIds());
        }

        $articleIds = array_merge($articleIds, $this->articles()->pluck('id')->toArray());

        return $articleIds;
    }

    public function allChildIds(): array
    {
        $articleCategoryIds = [];

        foreach ($this->childs as $child) {
            $articleCategoryIds = array_merge($articleCategoryIds, $child->allChildIds());
        }

        $articleCategoryIds = array_merge($articleCategoryIds, $this->childs()->pluck('id')->toArray());

        return $articleCategoryIds;
    }

    public function totalArticlesCount(): int
    {
        $amount = 0;

        foreach ($this->childs as $child) {
            $amount += $child->totalArticlesCount();
        }

        $amount += $this->articles()->count();

        return $amount;
    }

    public function getUrl($activeLocale = null, bool $native = true)
    {
        $originalLocale = app()->getLocale();

        if (!$activeLocale) {
            $activeLocale = $originalLocale;
        }

        $url = '';

        if (method_exists($this, 'parent') && $this->parent) {
            $url .= "{$this->parent->getUrl($activeLocale)}/";
        } else {
            if ($overviewPage = self::getOverviewPage()) {
                $url .= "{$overviewPage->getUrl($activeLocale)}/";
            }
        }

        $url .= $this->getTranslation('slug', $activeLocale);

        if (!str($url)->startsWith('/')) {
            $url = '/' . $url;
        }
        if ($activeLocale != Locales::getFirstLocale()['id'] && !str($url)->startsWith("/{$activeLocale}")) {
            $url = '/' . $activeLocale . $url;
        }

        return $native ? $url : url($url);
    }
}
