<?php

namespace Dashed\DashedArticles;

class ArticleManager
{
    protected static $builders = [
        'blocks' => [],
    ];

    public function builder(string $name, ?array $blocks = null): self|array
    {
        if (! $blocks) {
            return static::$builders[$name];
        }

        static::$builders[$name] = $blocks;

        return $this;
    }
}
