<?php

namespace Dashed\DashedArticles;

use Livewire\Livewire;
use Illuminate\Support\Facades\Gate;
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
        cms()->registerNavigationGroup('Artikelen', 20);

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

        cms()->registerResourceDocs(
            resource: \Dashed\DashedArticles\Filament\Resources\ArticleResource::class,
            title: 'Artikelen',
            intro: 'Beheer hier al je blogartikelen en nieuwsberichten. Per artikel leg je de inhoud, een uitgelichte afbeelding, een categorie, de auteur en de publicatiestatus vast. Je bepaalt ook de SEO-instellingen en voegt custom blokken toe om het artikel visueel op te bouwen.',
            sections: [
                [
                    'heading' => 'Wat kun je hier doen?',
                    'body' => <<<MARKDOWN
- Nieuwe artikelen aanmaken en bestaande artikelen bijwerken.
- Een uitgelichte afbeelding en content kiezen per artikel.
- Artikelen koppelen aan een categorie en auteur.
- De publicatiestatus instellen op concept, gepland of gepubliceerd.
- SEO-titel, omschrijving en social share afbeelding invullen.
- Custom blokken toevoegen om het artikel visueel op te bouwen.
MARKDOWN,
                ],
                [
                    'heading' => 'Wanneer gebruik je dit?',
                    'body' => 'Gebruik dit scherm zodra je een nieuw blogbericht of nieuwsartikel wilt publiceren op de website. Ook handig om bestaande artikelen aan te passen, te herpubliceren of tijdelijk offline te halen.',
                ],
            ],
            tips: [
                'Plan artikelen vooruit met een publicatiedatum in de toekomst.',
                'Koppel elk artikel aan een passende categorie zodat bezoekers makkelijk vinden wat ze zoeken.',
                'Herschrijf de SEO-omschrijving per artikel voor een hogere doorklik in zoekmachines.',
            ],
        );

        cms()->registerResourceDocs(
            resource: \Dashed\DashedArticles\Filament\Resources\ArticleCategoryResource::class,
            title: 'Artikel categorieen',
            intro: 'Hier beheer je de categorieen waaronder je artikelen vallen. Je kunt categorieen nesten in meerdere niveaus om een overzichtelijke structuur op te bouwen. Per categorie stel je SEO-gegevens in en bepaal je of de categorie uitgelicht wordt.',
            sections: [
                [
                    'heading' => 'Wat kun je hier doen?',
                    'body' => <<<MARKDOWN
- Nieuwe categorieen aanmaken voor je artikelen.
- Categorieen nesten onder een hoofdcategorie.
- Een categorie uitgelicht zetten zodat deze extra aandacht krijgt.
- SEO-titel en omschrijving per categorie invullen.
- Bestaande categorieen hernoemen of verwijderen.
MARKDOWN,
                ],
            ],
            tips: [
                'Houd categorienamen kort en duidelijk.',
                'Werk met maximaal twee niveaus diep voor een overzichtelijke navigatie.',
                'Markeer populaire categorieen als uitgelicht voor extra zichtbaarheid.',
            ],
        );

        cms()->registerResourceDocs(
            resource: \Dashed\DashedArticles\Filament\Resources\AuthorResource::class,
            title: 'Auteurs',
            intro: 'Beheer de auteurs die bij je artikelen horen. Per auteur leg je een naam, profielfoto en een korte bio vast, plus extra metadata zoals sociale kanalen. Zo krijgt ieder artikel een herkenbaar gezicht.',
            sections: [
                [
                    'heading' => 'Wat kun je hier doen?',
                    'body' => <<<MARKDOWN
- Nieuwe auteurs toevoegen met naam en foto.
- Een bio schrijven die onder artikelen getoond wordt.
- Aanvullende gegevens vastleggen zoals functie of sociale kanalen.
- Auteurs koppelen aan een of meerdere artikelen.
- Bestaande auteursprofielen bijwerken.
MARKDOWN,
                ],
            ],
            tips: [
                'Gebruik een vriendelijke, scherpe profielfoto voor herkenbaarheid.',
                'Houd de bio kort en vertel wat de auteur uniek maakt.',
                'Vul sociale kanalen in zodat lezers de auteur kunnen volgen.',
            ],
        );

        cms()->registerSettingsDocs(
            page: \Dashed\DashedArticles\Filament\Pages\Settings\ArticlesSettingsPage::class,
            title: 'Artikelen instellingen',
            intro: 'Op deze pagina koppel je de pagina\'s die het overzicht van je artikelen, categorieen en auteurs tonen. Daarnaast bepaal je hoe de URL\'s van artikelen worden opgebouwd. Deze instellingen zijn per site, dus je kunt voor elke website een andere blog opzet kiezen.',
            sections: [
                [
                    'heading' => 'Wat kun je hier instellen?',
                    'body' => 'Je wijst voor elke site aan welke pagina dient als artikel overzicht, als categorie pagina en als auteurspagina. Ook kies je of de categorie wel of niet in de URL van een artikel verschijnt.',
                ],
                [
                    'heading' => 'Hoe werkt de URL opbouw?',
                    'body' => <<<MARKDOWN
1. Maak in het pagina overzicht een pagina aan die dienst doet als artikel overzicht (bijvoorbeeld "Blog").
2. Selecteer die pagina hieronder bij **Overzichtspagina**. De URL van die pagina wordt automatisch het basisadres van al je artikelen.
3. Wil je categorieen in de URL? Zet dan de toggle aan. Een artikel krijgt dan een adres als `/blog/categorie/artikel-titel`.
4. Zet de toggle uit als je liever korte URL\'s hebt zoals `/blog/artikel-titel`.
5. Maak desgewenst losse pagina\'s voor categorie- en auteursoverzichten en koppel die in de bijbehorende velden.
MARKDOWN,
                ],
            ],
            fields: [
                'Artikel overzicht pagina' => 'De pagina die het overzicht van alle artikelen toont. De URL van deze pagina vormt het startpunt voor alle artikel adressen op deze site.',
                'Categorie pagina' => 'De pagina waarop bezoekers alle artikelen binnen een categorie kunnen bekijken. Laat leeg als je geen losse categoriepagina\'s gebruikt.',
                'Categorie in URL' => 'Aan zorgt dat de categorie als extra stuk in de URL verschijnt, bijvoorbeeld /blog/nieuws/artikel-titel. Uit geeft een kortere URL zonder categorie.',
                'Auteur overzicht pagina' => 'De pagina die alle artikelen van een auteur laat zien. Handig als je meerdere schrijvers hebt en bezoekers per auteur wil laten filteren.',
            ],
            tips: [
                'Verander de URL opbouw bij voorkeur niet meer als je site al online staat. Bestaande links en zoekresultaten kunnen anders gaan kapot.',
                'Heb je maar een auteur of categorie? Dan kun je de bijbehorende overzichtspagina\'s gewoon leeg laten.',
            ],
        );

        cms()->builder('blockDisabledForCache', [
            'all-articles',
            'all-authors',
        ]);
        Gate::policy(\Dashed\DashedArticles\Models\Article::class, \Dashed\DashedArticles\Policies\ArticlePolicy::class);
        Gate::policy(\Dashed\DashedArticles\Models\ArticleCategory::class, \Dashed\DashedArticles\Policies\ArticleCategoryPolicy::class);
        Gate::policy(\Dashed\DashedArticles\Models\ArticleAuthor::class, \Dashed\DashedArticles\Policies\ArticleAuthorPolicy::class);

        cms()->registerRolePermissions('Artikelen', [
            'view_article' => 'Artikelen bekijken',
            'edit_article' => 'Artikelen bewerken',
            'delete_article' => 'Artikelen verwijderen',
            'view_article_category' => 'Artikel categorieën bekijken',
            'edit_article_category' => 'Artikel categorieën bewerken',
            'delete_article_category' => 'Artikel categorieën verwijderen',
            'view_article_author' => 'Artikel auteurs bekijken',
            'edit_article_author' => 'Artikel auteurs bewerken',
            'delete_article_author' => 'Artikel auteurs verwijderen',
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
            __DIR__ . '/../resources/templates' => resource_path('views/' . config('dashed-core.site_theme', 'dashed')),
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
