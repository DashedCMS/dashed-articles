<?php

namespace Dashed\DashedArticles;

use App\Providers\AppServiceProvider;
use Dashed\DashedEcommerceCore\Models\Product;
use Dashed\DashedEcommerceCore\Models\ProductCategory;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Dashed\DashedArticles\Models\Article;
use Dashed\DashedArticles\Livewire\LikeArticle;
use Dashed\DashedArticles\Livewire\ShowArticles;
use Dashed\DashedArticles\Models\ArticleCategory;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Dashed\DashedArticles\Filament\Pages\Settings\ArticlesSettingsPage;

class DashedArticlesServiceProvider extends PackageServiceProvider
{
    public static string $name = 'dashed-articles';

    public function bootingPackage()
    {
        //Frontend components
        Livewire::component('articles.like-article', LikeArticle::class);
        Livewire::component('articles.show-articles', ShowArticles::class);
    }

    public function packageBooted()
    {
        if (!cms()->isCMSRoute() || app()->runningInConsole()) {
            return;
        }

        $defaultBlocks = [
            Block::make('all-articles')
                ->label('Alle artikelen')
                ->schema([
                ]),
            Block::make('few-articles')
                ->label('Paar artikelen')
                ->schema([
                    AppServiceProvider::getDefaultBlockFields(),
                    TextInput::make('title')
                        ->label('Titel'),
                    TextInput::make('subtitle')
                        ->label('Subtitel'),
                ]),
        ];

        cms()
            ->builder('blocks', $defaultBlocks);
    }

    public function configurePackage(Package $package): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        //        $this->loadViewsFrom(__DIR__.'/../resources/views', 'dashed-articles');

        $this->publishes([
            __DIR__ . '/../resources/templates' => resource_path('views/' . env('SITE_THEME', 'dashed')),
            __DIR__ . '/../resources/component-templates' => resource_path('views/components'),
        ], 'dashed-templates');

        //        $this->publishes([
        //            __DIR__ . '/../resources/views/frontend' => resource_path('views/vendor/dashed-articles/frontend'),
        //        ], 'dashed-articles-views');

        cms()->builder(
            'routeModels',
            [
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
            ]
        );

        cms()->builder(
            'settingPages',
            [
                'articles' => [
                    'name' => 'Artikelen',
                    'description' => 'Instellingen voor artikelen',
                    'icon' => 'rss',
                    'page' => ArticlesSettingsPage::class,
                ],
            ]
        );

        $package
            ->name(self::$name);
    }
}
