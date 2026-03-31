<?php

namespace Dashed\DashedArticles\Policies;

use Dashed\DashedCore\Policies\BaseResourcePolicy;

class ArticlePolicy extends BaseResourcePolicy
{
    protected function resourceName(): string
    {
        return 'Article';
    }
}
