<div class="py-16">
    <x-container>
        <div class="flex items-center gap-2">
            @if($articleAuthor->image)
                <x-dashed-files::image
                    class="h-24 rounded-full bg-primary"
                    :mediaId="$articleAuthor->image"
                    :alt="$articleAuthor->name"
                    :manipulations="[
                        'widen' => 200,
                    ]"
                />
            @endif
            <h1 class="text-3xl font-semibold tracking-tight text-primary-300 sm:text-5xl">
                {{ Translation::get('view-author-articles', 'author-articles', 'Bekijk de artikelen van :name:', 'text', [
            'name' => $articleAuthor->name
        ]) }}
            </h1>
        </div>
        <div
            class="mt-8 mx-auto grid max-w-2xl auto-rows-fr grid-cols-1 gap-8 sm:mt-20 lg:mx-0 lg:max-w-none lg:grid-cols-3">
            @foreach($articles as $article)
                <x-article :article="$article"/>
            @endforeach
        </div>
        <div class="mt-4">
            {{ $articles->links('dashed.partials.pagination') }}
        </div>
    </x-container>
</div>
