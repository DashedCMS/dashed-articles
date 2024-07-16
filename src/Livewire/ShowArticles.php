<?php

namespace Dashed\DashedArticles\Livewire;

use Dashed\DashedArticles\Models\Article;
use Dashed\DashedArticles\Models\ArticleLike;
use Illuminate\Support\Collection;
use Livewire\Component;

class ShowArticles extends Component
{
    public Collection $articles;

    public function mount(Article $article)
    {
    }

    public function render()
    {
        return view('dashed-articles::frontend.show-articles');
    }
}
