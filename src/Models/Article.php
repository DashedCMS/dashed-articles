<?php

namespace Dashed\DashedArticles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Dashed\DashedCore\Classes\Sites;
use Dashed\DashedCore\Models\Concerns\HasCustomBlocks;
use Dashed\DashedCore\Models\Concerns\IsVisitable;
use Dashed\DashedCore\Models\Customsetting;
use Dashed\DashedPages\Models\Page;
use Spatie\SchemaOrg\Schema;

class Article extends Model
{
    use SoftDeletes;
    use IsVisitable;
    use HasCustomBlocks;

    protected $table = 'dashed__articles';

    public $translatable = [
        'name',
        'slug',
        'content',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'blocks' => 'array',
        'site_ids' => 'array',
    ];

    protected $appends = [
        'status',
        'readingTimeMinutes',
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
        return floor(str_word_count(strip_tags($this->getRawOriginal('content') . json_encode($this->contentBlocks))) / 200);
    }

    public static function resolveRoute($parameters = [])
    {
        $slug = $parameters['slug'] ?? '';
        $slugComponents = explode('/', $slug);

        if ($slug && $overviewPage = self::getOverviewPage()) {
            $article = Article::publicShowable()->where('slug->' . App::getLocale(), $slugComponents[count($slugComponents) - 1])->first();
            if ($article) {
                $page = Page::publicShowable()->isNotHome()->where('slug->' . App::getLocale(), str_replace('/' . $slugComponents[count($slugComponents) - 1], '', $slug))->where('id', $overviewPage->id)->first();
                if ($page) {
                    if (View::exists('dashed.articles.show')) {
                        seo()->metaData('metaTitle', $article->metadata && $article->metadata->title ? $article->metadata->title : $article->name);
                        seo()->metaData('metaDescription', $article->metadata->description ?? '');
                        seo()->metaData('ogType', 'article');
                        if ($article->metadata && $article->metadata->image) {
                            seo()->metaData('metaImage', $article->metadata->image);
                        }

                        $articleSchema = Schema::article()
                            ->name(seo()->metaData('metaTitle'))
                            ->url(request()->url())
                            ->image(seo()->metaData('metaImage'))
                            ->description($article->contentBlocks['excerpt'] ?? '')
                            ->author($article->author ? $article->author->name : Customsetting::get('site_name'))
                            ->publisher($article->author ? $article->author->name : Customsetting::get('site_name'))
                            ->dateCreated($article->created_at)
                            ->dateModified($article->updated_at)
                            ->datePublished($article->start_date ?: $article->created_at)
                            ->inLanguage(LaravelLocalization::getCurrentLocaleName())
                            ->thumbnailUrl(seo()->metaData('metaImage'))
                            ->timeRequired("PT{$article->readingTimeMinutes}M");

                        $schemas = seo()->metaData('schemas');
                        $schemas['article'] = $articleSchema;
                        seo()->metaData('schemas', $schemas);

                        $correctLocale = App::getLocale();
                        $alternateUrls = [];
                        foreach (Sites::getLocales() as $locale) {
                            if ($locale['id'] != $correctLocale) {
                                LaravelLocalization::setLocale($locale['id']);
                                App::setLocale($locale['id']);
                                $alternateUrls[$locale['id']] = $article->getUrl();
                            }
                        }
                        LaravelLocalization::setLocale($correctLocale);
                        App::setLocale($correctLocale);
                        seo()->metaData('alternateUrls', $alternateUrls);

                        View::share('article', $article);
                        View::share('breadcrumbs', $article->breadcrumbs());
                        View::share('page', $page);

                        return view('dashed.articles.show');
                    } else {
                        return 'pageNotFound';
                    }
                }
            }
        }
    }
}
