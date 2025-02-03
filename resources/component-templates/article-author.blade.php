<div class="rounded-2xl bg-primary-200 px-8 py-10">
    @if($author->image)
        <x-dashed-files::image
            class="mx-auto size-48 rounded-full md:size-56"
            :mediaId="$author->image"
            :manipulations="[
                'widen' => 400,
            ]"
        />
    @endif
    <h3 class="mt-6 text-base/7 font-semibold tracking-tight text-white">{{ $author->name }}</h3>
    <p class="text-sm/6 text-gray-400">{{ Translation::get('article-count', 'author-articles', ':count: artikelen', 'text', [
    'count' => $author->articles->count()
]) }}</p>

    <a href="{{ $author->url }}" class="mt-6 button button--primary">
        {{ Translation::get('view-articles', 'author-articles', 'Bekijk artikelen') }}
    </a>
</div>
