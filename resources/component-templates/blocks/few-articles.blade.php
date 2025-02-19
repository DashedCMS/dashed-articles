<div class="@if($data['top_margin'] ?? true) pt-16 sm:pt-24 @endif @if($data['bottom_margin'] ?? true) pb-16 sm:pb-24 @endif">
    <x-container :show="$data['in_container'] ?? true">
        @if(isset($data['title']))
            <header class="flex flex-wrap gap-4 items-center justify-between">
                <div>
                    <h2 class="shrink-0 tracking-tight text-4xl text-balance font-brand">
                        {{ $data['title'] }}
                    </h2>
                    @if($data['subtitle'] ?? false)
                        <p class="mt-2 text-lg leading-8 text-black">{{ $data['subtitle'] }}</p>
                    @endif
                </div>
                <div>
                    <a class="button button--primary" href="{{ \Dashed\DashedArticles\Models\Article::getOverviewPage()->url ?? '#' }}">{{ Translation::get('view-all-articles', 'articles', 'Bekijk alle artikelen') }}</a>
                </div>
            </header>
        @endif
        <div class="mx-auto grid max-w-2xl auto-rows-fr grid-cols-1 gap-8 mt-12 lg:mx-0 lg:max-w-none lg:grid-cols-3">
            @foreach(Articles::get(3, 'created_at', 'DESC', $hideArticleId ?? 0) as $article)
                <x-article :article="$article" />
            @endforeach
        </div>
    </x-container>
</div>
