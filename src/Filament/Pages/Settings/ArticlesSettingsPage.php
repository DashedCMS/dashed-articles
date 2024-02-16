<?php

namespace Dashed\DashedArticles\Filament\Pages\Settings;

use Filament\Pages\Page;
use Filament\Forms\Components\Tabs;
use Dashed\DashedCore\Classes\Sites;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Notifications\Notification;
use Dashed\DashedCore\Models\Customsetting;
use Filament\Forms\Concerns\InteractsWithForms;
use Dashed\DashedPages\Models\Page as PageModel;

class ArticlesSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Artikelen';

    protected static string $view = 'dashed-core::settings.pages.default-settings';
    public array $data = [];

    public function mount(): void
    {
        $formData = [];
        $sites = Sites::getSites();
        foreach ($sites as $site) {
            $formData["article_overview_page_id_{$site['id']}"] = Customsetting::get('article_overview_page_id', $site['id']);
            $formData["articlecategory_overview_page_id_{$site['id']}"] = Customsetting::get('articlecategory_overview_page_id', $site['id']);
        }

        $this->form->fill($formData);
    }

    protected function getFormSchema(): array
    {
        $sites = Sites::getSites();
        $tabGroups = [];

        $tabs = [];
        foreach ($sites as $site) {
            $schema = [
                Select::make("article_overview_page_id_{$site['id']}")
                    ->label('Artikel overview pagina')
                    ->options(PageModel::thisSite($site['id'])->pluck('name', 'id')),
                Select::make("articlecategory_overview_page_id_{$site['id']}")
                    ->label('Artikel category overview pagina')
                    ->options(PageModel::thisSite($site['id'])->pluck('name', 'id')),
            ];

            $tabs[] = Tab::make($site['id'])
                ->label(ucfirst($site['name']))
                ->schema($schema)
                ->columns([
                    'default' => 1,
                    'lg' => 2,
                ]);
        }
        $tabGroups[] = Tabs::make('Sites')
            ->tabs($tabs);

        return $tabGroups;
    }

    public function getFormStatePath(): ?string
    {
        return 'data';
    }

    public function submit()
    {
        $sites = Sites::getSites();

        foreach ($sites as $site) {
            Customsetting::set('article_overview_page_id', $this->form->getState()["article_overview_page_id_{$site['id']}"], $site['id']);
            Customsetting::set('articlecategory_overview_page_id', $this->form->getState()["articlecategory_overview_page_id_{$site['id']}"], $site['id']);
        }

        Notification::make()
            ->title('De artikel instellingen zijn opgeslagen')
            ->success()
            ->send();

        return redirect(ArticlesSettingsPage::getUrl());
    }
}
