<?php

namespace Dashed\DashedArticles\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Dashed\DashedArticles\Models\ArticleAuthor;

class ShowAuthors extends Component
{
    use WithPagination;

    public int $pagination = 12;


    public ?string $search = null;

    public string $sort = 'latest';
    public array $blockData = [];

    public function mount(array $blockData = [], int $pagination = 12, ?string $search = null, string $sort = 'latest')
    {
        $this->pagination = $pagination;
        $this->search = $search;
        $this->sort = $sort;
        $this->blockData = $blockData;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $search = $this->search;
        $sort = $this->sort;
        $pagination = $this->pagination;

        return view(config('dashed-core.site_theme') . '.article-author.show-authors', [
            'authors' => ArticleAuthor::query()
                ->publicShowable()
                ->when($search, function ($query, $search) {
                    return $query->search($search);
                })
                ->when($sort === 'latest', function ($query) {
                    return $query->latest();
                })
                ->paginate($pagination),
        ]);
    }
}
