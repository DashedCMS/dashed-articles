<?php

namespace Dashed\DashedArticles\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Locked;
use Dashed\DashedArticles\Models\Article;
use Dashed\DashedArticles\Models\ArticleAuthor;
use Dashed\DashedArticles\Models\ArticleCategory;

class ShowArticles extends Component
{
    use WithPagination;

    public int $pagination = 12;

    public ?int $category = null;
    public ?array $categoryIds = [];

    public ?string $search = null;

    public string $sort = 'latest';
    // Worden alleen bij het mounten gezet; de browser hoort ze niet te kunnen overschrijven.
    #[Locked]
    public ?int $authorId = null;
    #[Locked]
    public array $blockData = [];

    public function mount(int $pagination = 12, ?int $category = null, ?string $search = null, string $sort = 'latest', ?int $authorId = null, array $blockData = [])
    {
        $this->pagination = $pagination;
        $this->category = $category;
        if ($category) {
            $categoryIds = [$category];
            $categoryModel = ArticleCategory::find($category);
            if ($categoryModel) {
                $childIds = $categoryModel->allChildIds();
                $categoryIds = array_merge($categoryIds, $childIds);
            }
            $this->categoryIds = $categoryIds;
        }
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
        // $categoryIds is publiek en dus door de client te zetten: alleen platte id's doorlaten,
        // anders gooit whereIn() op geneste arrays.
        $categoryIds = array_values(array_filter((array) $this->categoryIds, 'is_numeric'));
        $search = $this->search;
        $sort = $this->sort;
        $pagination = $this->pagination;
        $authorId = $this->authorId;

        $view = $authorId ? 'show-author-articles' : 'show-articles';

        return view(config('dashed-core.site_theme', 'dashed') . '.articles.' . $view, [
            // De auteursview leest $articleAuthor. Bij de eerste render komt die uit de pagina,
            // maar bij een Livewire-update (pagineren) bestond hij niet meer.
            ...($authorId ? ['articleAuthor' => ArticleAuthor::find($authorId)] : []),
            'articles' => Article::query()
                ->publicShowable()
                ->when($authorId, function ($query, $authorId) {
                    return $query->where('author_id', $authorId);
                })
                ->when($categoryIds, function ($query, $categoryIds) {
                    return $query->whereIn('category_id', $categoryIds);
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
