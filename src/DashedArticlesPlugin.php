<?php

namespace Dashed\DashedArticles;

use Filament\Panel;
use Filament\Contracts\Plugin;
use Dashed\DashedArticles\Filament\Resources\AuthorResource;
use Dashed\DashedArticles\Filament\Resources\ArticleResource;
use Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource;
use Dashed\DashedArticles\Filament\Pages\Settings\ArticlesSettingsPage;

class DashedArticlesPlugin implements Plugin
{
    public function getId(): string
    {
        return 'dashed-articles';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                ArticleResource::class,
                ArticleCategoryResource::class,
                AuthorResource::class,
            ])
            ->pages([
                ArticlesSettingsPage::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
    }
}
