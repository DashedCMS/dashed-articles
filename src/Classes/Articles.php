<?php

namespace Dashed\DashedArticles\Classes;

use Dashed\DashedArticles\Models\Article;

class Articles
{
    public static function get($limit = 4, $orderBy = 'created_at', $order = 'DESC', int $exceptId = 0, array $categoryIds = [])
    {
        $articles = Article::search()->where('id', '!=', $exceptId)->thisSite()->publicShowable();

        if ($categoryIds) {
            $articles->whereIn('category_id', $categoryIds);
        }

        return $articles->limit($limit)->orderBy($orderBy, $order)->get();
    }

    public static function getAll($pagination = 12, $orderBy = 'created_at', $order = 'DESC')
    {
        return Article::thisSite()->publicShowable()->orderBy($orderBy, $order)->paginate($pagination)->withQueryString();
    }

    public static function getOverviewUrl(): ?string
    {
        return Article::getOverviewPage()->getUrl() ?? '#';
    }
}
