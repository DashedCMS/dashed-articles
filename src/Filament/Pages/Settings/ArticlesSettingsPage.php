<?php

namespace Dashed\DashedArticles\Filament\Pages\Settings;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Dashed\DashedCore\Classes\Sites;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Contracts\HasSchemas;
use Dashed\DashedCore\Models\Customsetting;
use Dashed\DashedPages\Models\Page as PageModel;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class ArticlesSettingsPage extends Page implements HasSchemas
{
    use InteractsWithSchemas;
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Artikelen';

    protected string $view = 'dashed-core::settings.pages.default-settings';

    public array $data = [];

    public function mount(): void
    {
        $formData = [];
        $sites = Sites::getSites();
        foreach ($sites as $site) {
            $formData["article_overview_page_id_{$site['id']}"] = Customsetting::get('article_overview_page_id', $site['id']);
            $formData["article_author_overview_page_id_{$site['id']}"] = Customsetting::get('article_author_overview_page_id', $site['id']);
            $formData["article_category_overview_page_id_{$site['id']}"] = Customsetting::get('article_category_overview_page_id', $site['id']);
            $formData["article_use_category_in_url_{$site['id']}"] = Customsetting::get('article_use_category_in_url', $site['id']);
        }

        $this->form->fill($formData);
    }

    public function form(Schema $schema): Schema
    {
        $sites = Sites::getSites();
        $tabGroups = [];

        $tabs = [];
        foreach ($sites as $site) {
            $newSchema = [
                Select::make("article_overview_page_id_{$site['id']}")
                    ->label('Artikel overview pagina')
                    ->searchable()
                    ->preload()
                    ->options(PageModel::thisSite($site['id'])->pluck('name', 'id')),
                Select::make("article_category_overview_page_id_{$site['id']}")
                    ->label('Artikel category overview pagina')
                    ->searchable()
                    ->preload()
                    ->options(PageModel::thisSite($site['id'])->pluck('name', 'id')),
                Toggle::make("article_use_category_in_url_{$site['id']}")
                    ->label('Gebruik categorie in url'),
                Select::make("article_author_overview_page_id_{$site['id']}")
                    ->label('Artikel auteurs overview pagina')
                    ->searchable()
                    ->preload()
                    ->options(PageModel::thisSite($site['id'])->pluck('name', 'id')),
            ];

            $tabs[] = Tab::make($site['id'])
                ->label(ucfirst($site['name']))
                ->schema($newSchema)
                ->columns([
                    'default' => 1,
                    'lg' => 2,
                ]);
        }
        $tabGroups[] = Tabs::make('Sites')
            ->tabs($tabs);

        return $schema->schema($tabGroups)
            ->statePath('data');
    }

    public function submit()
    {
        $sites = Sites::getSites();

        foreach ($sites as $site) {
            Customsetting::set('article_overview_page_id', $this->form->getState()["article_overview_page_id_{$site['id']}"], $site['id']);
            Customsetting::set('article_author_overview_page_id', $this->form->getState()["article_author_overview_page_id_{$site['id']}"], $site['id']);
            Customsetting::set('article_category_overview_page_id', $this->form->getState()["article_category_overview_page_id_{$site['id']}"], $site['id']);
            Customsetting::set('article_use_category_in_url', $this->form->getState()["article_use_category_in_url_{$site['id']}"], $site['id']);
        }

        Notification::make()
            ->title('De artikel instellingen zijn opgeslagen')
            ->success()
            ->send();

        return redirect(ArticlesSettingsPage::getUrl());
    }
}
