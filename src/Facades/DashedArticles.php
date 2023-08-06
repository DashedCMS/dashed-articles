<?php

namespace Dashed\DashedArticles\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dashed\DashedArticles\DashedArticles
 */
class DashedArticles extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dashed-articles';
    }
}
