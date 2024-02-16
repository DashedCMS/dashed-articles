<div x-data="{ like: @entangle('like') }">
    <h3 class="text-lg md:text-xl font-bold tracking-tight">{{ Translation::get('article-liked', 'article', 'Did you like this article?') }}</h3>
    <div class="flex items-center mt-4 gap-4">
        <button wire:click="markAs(1)" class="border-4 button button--primary gap-4" :class="like === 1 ? 'border-primary-400' : ''">
            <x-lucide-thumbs-up class="size-6" />
            <span>{{ Translation::get('article-like-yes', 'article', 'Yes') }}</span>
        </button>
        <button wire:click="markAs(0)" class="border-4 button button--primary gap-4" :class="like === 0 ? 'border-primary-400' : ''">
            <x-lucide-thumbs-down class="size-6" />
            <span>{{ Translation::get('article-like-no', 'article', 'No') }}</span>
        </button>
    </div>
    <p>{{ Translation::get('amount-liked-this-article', 'article', ':totalLikes: out of :total: liked this article', 'text', [
    'totalLikes' => $totalLikes,
    'total' => $totalLikes + $totalDislikes
]) }}</p>
</div>