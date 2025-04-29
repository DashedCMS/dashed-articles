<?php

namespace Dashed\DashedArticles;

use Livewire\Livewire;
use App\Providers\AppServiceProvider;
use Dashed\DashedCore\Classes\Locales;
use Spatie\LaravelPackageTools\Package;
use Filament\Forms\Components\TextInput;
use Dashed\DashedArticles\Models\Article;
use Filament\Forms\Components\Builder\Block;
use Dashed\DashedArticles\Livewire\LikeArticle;
use Dashed\DashedArticles\Livewire\ShowAuthors;
use Dashed\DashedArticles\Models\ArticleAuthor;
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
        Livewire::component('articles.show-authors', ShowAuthors::class);

        //        if (config('dashed-articles.registerDefaultBuilderBlocks', true)) {
        //            cms()->builder('builderBlockClasses', [
        //                self::class => 'builderBlocks',
        //            ]);
        //        }

        cms()->builder('publishOnUpdate', [
            'dashed-articles-config',
        ]);

        cms()->builder('createDefaultPages', [
            self::class => 'createDefaultPages',
        ]);

        cms()->builder('plugins', [
            new DashedArticlesPlugin(),
        ]);

        cms()->builder('blockDisabledForCache', [
            'all-articles',
            'all-authors',
        ]);
    }

    public static function builderBlocks()
    {
        $defaultBlocks = [
            Block::make('all-articles')
                ->label('Alle artikelen')
                ->schema([]),
            Block::make('few-articles')
                ->label('Paar artikelen')
                ->schema([
                    AppServiceProvider::getDefaultBlockFields(),
                    TextInput::make('title')
                        ->label('Titel'),
                    TextInput::make('subtitle')
                        ->label('Subtitel'),
                ]),
            Block::make('all-authors')
                ->label('Alle auteurs')
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

        cms()->registerRouteModel(Article::class, 'Artikel', 'Artikelen');
        cms()->registerRouteModel(ArticleCategory::class, 'Artikel categorie', 'Artikel categorieen');
        cms()->registerRouteModel(ArticleAuthor::class, 'Artikel auteur', 'Artikel auteurs');
        cms()->registerSettingsPage(ArticlesSettingsPage::class, 'Artikel');

        $package
            ->hasConfigFile([
                'dashed-articles',
            ])
            ->name(self::$name);
    }

    public static function createDefaultPages(): void
    {
        if (! \Dashed\DashedCore\Models\Customsetting::get('article_overview_page_id')) {
            $page = new \Dashed\DashedPages\Models\Page();
            foreach (Locales::getActivatedLocalesFromSites() as $locale) {
                $page->setTranslation('name', $locale, 'Artikelen');
                $page->setTranslation('slug', $locale, 'artikelen');
                $page->setTranslation('content', $locale, [
                    [
                        'data' => [
                            'in_container' => true,
                            'top_margin' => true,
                            'bottom_margin' => true,
                        ],
                        'type' => 'all-articles',
                    ],
                ]);
            }
            $page->save();

            \Dashed\DashedCore\Models\Customsetting::set('article_overview_page_id', $page->id);
        }

        if (! \Dashed\DashedCore\Models\Customsetting::get('article_author_overview_page_id')) {
            $page = new \Dashed\DashedPages\Models\Page();
            foreach (Locales::getActivatedLocalesFromSites() as $locale) {
                $page->setTranslation('name', $locale, 'Auteurs');
                $page->setTranslation('slug', $locale, 'auteurs');
                $page->setTranslation('content', $locale, [
                    [
                        'data' => [
                            'in_container' => true,
                            'top_margin' => true,
                            'bottom_margin' => true,
                        ],
                        'type' => 'all-authors',
                    ],
                ]);
            }
            $page->save();

            \Dashed\DashedCore\Models\Customsetting::set('article_author_overview_page_id', $page->id);
        }
    }
}
