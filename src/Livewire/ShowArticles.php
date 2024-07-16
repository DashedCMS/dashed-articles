<?php

namespace Dashed\DashedArticles\Livewire;

use Dashed\DashedArticles\Models\Article;
use Dashed\DashedArticles\Models\ArticleLike;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ShowArticles extends Component
{
    use WithPagination;

    public int $pagination = 12;
    public ?int $category = null;
    public ?string $search = null;
    public string $sort = 'latest';

    public function mount(int $pagination = 12, ?int $category = null, ?string $search = null, string $sort = 'latest')
    {
        $this->pagination = $pagination;
        $this->category = $category;
        $this->search = $search;
        $this->sort = $sort;
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

        return view('dashed-articles::frontend.show-articles', [
            'articles' => Article::query()
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
                ->paginate($pagination)
        ]);
    }
}
