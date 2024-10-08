<div x-data="{ like: @entangle('like') }" class="grid gap-4">
    <h3 class="text-lg md:text-xl font-bold tracking-tight">{{ Translation::get('article-liked', 'article', 'Vond je dit een leuk artikel?') }}</h3>
    <div class="flex items-center gap-4">
        <button wire:click="markAs(1)" class="border-4 button button--primary gap-4" :class="like === 1 ? 'border-primary-400' : ''">
            <x-lucide-thumbs-up class="size-6" />
            <span>{{ Translation::get('article-like-yes', 'article', 'Ja') }}</span>
        </button>
        <button wire:click="markAs(0)" class="border-4 button button--primary gap-4" :class="like === 0 ? 'border-primary-400' : ''">
            <x-lucide-thumbs-down class="size-6" />
            <span>{{ Translation::get('article-like-no', 'article', 'Nee') }}</span>
        </button>
    </div>
    <p class="text-neutral-600">{{ Translation::get('amount-liked-this-article', 'article', ':totalLikes: van :total: vonden dit een leuk artikel', 'text', [
    'totalLikes' => $totalLikes,
    'total' => $totalLikes + $totalDislikes
]) }}</p>
</div>
