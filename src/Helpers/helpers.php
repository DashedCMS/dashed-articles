<?php

use Dashed\DashedArticles\ArticleManager;

if (! function_exists('articles')) {
    function articles(): ArticleManager
    {
        return app(ArticleManager::class);
    }
}
