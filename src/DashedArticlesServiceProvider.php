<?php

namespace Dashed\DashedArticles;

use Filament\PluginServiceProvider;
use Dashed\DashedArticles\Filament\Pages\Settings\ArticlesSettingsPage;
use Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource;
use Dashed\DashedArticles\Filament\Resources\ArticleResource;
use Dashed\DashedArticles\Filament\Resources\AuthorResource;
use Dashed\DashedArticles\Models\Article;
use Dashed\DashedArticles\Models\ArticleCategory;
use Spatie\LaravelPackageTools\Package;

class DashedArticlesServiceProvider extends PluginServiceProvider
{
    public static string $name = 'dashed-articles';

    public function configurePackage(Package $package): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        cms()->builder(
            'routeModels',
            array_merge(cms()->builder('routeModels'), [
                'article' => [
                    'name' => 'Artikel',
                    'pluralName' => 'Artikelen',
                    'class' => Article::class,
                    'nameField' => 'name',
                ],
                'articleCategory' => [
                    'name' => 'Artikel categorie',
                    'pluralName' => 'Artikel categorieen',
                    'class' => ArticleCategory::class,
                    'nameField' => 'name',
                ],
            ])
        );

        cms()->builder(
            'settingPages',
            array_merge(cms()->builder('settingPages'), [
                'articles' => [
                    'name' => 'Artikelen',
                    'description' => 'Instellingen voor artikelen',
                    'icon' => 'rss',
                    'page' => ArticlesSettingsPage::class,
                ],
            ])
        );

        $package
            ->name('dashed-articles');
    }

    protected function getPages(): array
    {
        return array_merge(parent::getPages(), [
            ArticlesSettingsPage::class,
        ]);
    }

    protected function getResources(): array
    {
        return array_merge(parent::getResources(), [
            ArticleResource::class,
            ArticleCategoryResource::class,
            AuthorResource::class,
        ]);
    }
}
