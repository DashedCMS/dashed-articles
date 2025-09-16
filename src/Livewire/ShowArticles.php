<?php

namespace Dashed\DashedArticles\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Dashed\DashedArticles\Models\Article;

class ShowArticles extends Component
{
    use WithPagination;

    public int $pagination = 12;

    public ?int $category = null;

    public ?string $search = null;

    public string $sort = 'latest';
    public ?int $authorId = null;
    public array $blockData = [];

    public function mount(int $pagination = 12, ?int $category = null, ?string $search = null, string $sort = 'latest', ?int $authorId = null, array $blockData = [])
    {
        $this->pagination = $pagination;
        $this->category = $category;
        $this->search = $search;
        $this->sort = $sort;
        $this->authorId = $authorId;
        $this->blockData = $blockData;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $category = $this->category;
        $search = $this->search;
        $sort = $this->sort;
        $pagination = $this->pagination;
        $authorId = $this->authorId;

        $view = $authorId ? 'show-author-articles' : 'show-articles';

        return view(config('dashed-core.site_theme') . '.articles.' . $view, [
            'articles' => Article::query()
                ->publicShowable()
                ->when($authorId, function ($query, $authorId) {
                    return $query->where('author_id', $authorId);
                })
                ->when($category, function ($query, $category) {
                    return $query->where('category_id', $category);
                })
                ->when($search, function ($query, $search) {
                    return $query->search($search);
                })
                ->when($sort === 'latest', function ($query) {
                    return $query->latest();
                })
                ->when($sort === 'popular', function ($query) {
                    return $query->withCount('likes')->orderBy('likes_count', 'desc');
                })
                ->paginate($pagination),
        ]);
    }
}
