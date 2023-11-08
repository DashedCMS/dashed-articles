<?php

namespace Dashed\DashedArticles;

use Spatie\LaravelPackageTools\Package;
use Dashed\DashedArticles\Models\Article;
use Dashed\DashedArticles\Models\ArticleCategory;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Dashed\DashedArticles\Filament\Pages\Settings\ArticlesSettingsPage;

class DashedArticlesServiceProvider extends PackageServiceProvider
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
            ->name(self::$name);
    }
}
