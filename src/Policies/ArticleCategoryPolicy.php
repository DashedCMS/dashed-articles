<?php

namespace Dashed\DashedArticles\Policies;

use Dashed\DashedCore\Policies\BaseResourcePolicy;

class ArticleCategoryPolicy extends BaseResourcePolicy
{
    protected function resourceName(): string
    {
        return 'ArticleCategory';
    }
}
