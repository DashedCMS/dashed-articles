<?php

namespace Dashed\DashedArticles\Policies;

use Dashed\DashedCore\Policies\BaseResourcePolicy;

class ArticleAuthorPolicy extends BaseResourcePolicy
{
    protected function resourceName(): string
    {
        return 'ArticleAuthor';
    }
}
