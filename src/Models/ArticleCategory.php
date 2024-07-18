<?php

namespace Dashed\DashedArticles\Models;

use Dashed\DashedCore\Models\Concerns\HasCustomBlocks;
use Dashed\DashedPages\Models\Page;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;
use Dashed\DashedCore\Classes\Sites;
use Illuminate\Support\Facades\View;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Dashed\DashedCore\Models\Customsetting;
use Dashed\DashedCore\Models\Concerns\IsVisitable;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class ArticleCategory extends Model
{
    use HasTranslations;
    use IsVisitable;
    use HasCustomBlocks;

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
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public static function resolveRoute($parameters = [])
    {
        $slug = $parameters['slug'] ?? '';
        $slugComponents = explode('/', $slug);

        $slug = $parameters['slug'] ?? '';
        $overviewPage = self::getOverviewPage();
        if ($overviewPage) {
            $page = Page::publicShowable()->isNotHome()->where('slug->' . App::getLocale(), str($slugComponents[0])->replace('/', ''))->where('id', $overviewPage->id)->first();
            if (!$page) {
                return;
            }
            unset($slugComponents[0]);
        }

        if ($slug) {
            if (!$slugComponents) {
                $parentId = null;
                foreach ($slugComponents as $slugPart) {
                    $articleCategory = ArticleCategory::publicShowable()->where('slug->' . app()->getLocale(), $slugPart)->where('parent_id', $parentId)->first();
                    $parentId = $articleCategory?->id;
                    if (!$articleCategory) {
                        return;
                    }
                }

                if (View::exists(Customsetting::get('site_theme', null, 'dashed') . '.article-categories.show-overview')) {
                    seo()->metaData('metaTitle', $page->metadata && $page->metadata->title ? $page->metadata->title : $page->name);
                    seo()->metaData('metaDescription', $page->metadata->description ?? '');
                    seo()->metaData('ogType', 'article');
                    if ($page->metadata && $page->metadata->image) {
                        seo()->metaData('metaImage', $page->metadata->image);
                    }

                    $correctLocale = App::getLocale();
                    $alternateUrls = [];
                    foreach (Sites::getLocales() as $locale) {
                        if ($locale['id'] != $correctLocale) {
                            LaravelLocalization::setLocale($locale['id']);
                            App::setLocale($locale['id']);
                            $alternateUrls[$locale['id']] = $page->getUrl();
                        }
                    }
                    LaravelLocalization::setLocale($correctLocale);
                    App::setLocale($correctLocale);
                    seo()->metaData('alternateUrls', $alternateUrls);

                    return [
                        'view' => Customsetting::get('site_theme', null, 'dashed') . '.article-categories.show-overview',
                        'parameters' => [
                            'page' => $page,
                            'breadcrumbs' => $page->breadcrumbs(),
                            'categories' => ArticleCategory::publicShowable()->paginate(12),
                        ]
                    ];
                    return view(Customsetting::get('site_theme', null, 'dashed') . '.article-categories.show-overview');
                } else {
                    return 'pageNotFound';
                }
            } else {
                $parentId = null;
                foreach ($slugComponents as $slugPart) {
                    $articleCategory = ArticleCategory::publicShowable()->where('slug->' . app()->getLocale(), $slugPart)->where('parent_id', $parentId)->first();
                    $parentId = $articleCategory?->id;
                    if (!$articleCategory) {
                        return;
                    }
                }

                if (View::exists(Customsetting::get('site_theme', null, 'dashed') . '.article-categories.show')) {
                    seo()->metaData('metaTitle', $articleCategory->metadata && $articleCategory->metadata->title ? $articleCategory->metadata->title : $articleCategory->name);
                    seo()->metaData('metaDescription', $articleCategory->metadata->description ?? '');
                    seo()->metaData('ogType', 'article');
                    if ($articleCategory->metadata && $articleCategory->metadata->image) {
                        seo()->metaData('metaImage', $articleCategory->metadata->image);
                    }

                    $correctLocale = App::getLocale();
                    $alternateUrls = [];
                    foreach (Sites::getLocales() as $locale) {
                        if ($locale['id'] != $correctLocale) {
                            LaravelLocalization::setLocale($locale['id']);
                            App::setLocale($locale['id']);
                            $alternateUrls[$locale['id']] = $articleCategory->getUrl();
                        }
                    }
                    LaravelLocalization::setLocale($correctLocale);
                    App::setLocale($correctLocale);
                    seo()->metaData('alternateUrls', $alternateUrls);

                    return [
                        'view' => Customsetting::get('site_theme', null, 'dashed') . '.article-categories.show',
                        'parameters' => [
                            'articleCategory' => $articleCategory,
                            'breadcrumbs' => $articleCategory->breadcrumbs(),
                            'articles' => $articleCategory->articles()->paginate(12),
                            'page' => $page ?? null,
                        ]
                    ];

                    return view(Customsetting::get('site_theme', null, 'dashed') . '.article-categories.show');
                } else {
                    return 'pageNotFound';
                }
            }
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

    public function getUrl()
    {
        $url = '';

        if ($overviewPage = self::getOverviewPage()) {
            if (method_exists($this, 'parent') && $this->parent) {
                $url .= "{$this->parent->getUrl()}/";
            } else {
                $url .= "{$overviewPage->getUrl()}/";
            }
        }

        $url .= $this->slug;

        return LaravelLocalization::localizeUrl($url);
    }
}
