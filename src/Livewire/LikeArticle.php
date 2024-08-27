<?php

namespace Dashed\DashedArticles\Livewire;

use Dashed\DashedArticles\Models\Article;
use Dashed\DashedArticles\Models\ArticleLike;
use Livewire\Component;

class LikeArticle extends Component
{
    public Article $article;

    public int $like = 2;

    public int $totalLikes = 0;

    public int $totalDislikes = 0;

    public function mount(Article $article)
    {
        $this->article = $article;
        $this->updateStats();
    }

    public function markAs(bool $status): void
    {
        if (ArticleLike::where('article_id', $this->article->id)->where('like', $status)->exists()) {
            ArticleLike::remove($this->article->id);
        } else {
            ArticleLike::markAs($status, $this->article->id);
        }
        $this->updateStats();
    }

    public function updateStats()
    {
        $this->like = ArticleLike::where('article_id', $this->article->id)->first()?->like ?? 2;
        $this->totalLikes = $this->article->likes()->count();
        $this->totalDislikes = $this->article->dislikes()->count();
    }

    public function render()
    {
        return view('dashed-articles::frontend.like-article');
    }
}
