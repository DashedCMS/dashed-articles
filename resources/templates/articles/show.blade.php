<x-master>
    <article class="relative py-16">
        <x-container>
            <header class="flex flex-col items-center text-center">
                <time class="inline-block font-bold text-primary">
                    {{ $article->start_date?->format('d/m/Y') ?: $article->created_at?->format('d/m/Y')}}
                </time>

                <h1 class="mt-4 text-3xl md:text-4xl lg:text-5xl font-bold tracking-tight max-w-[30ch]">
                    {{ $article->name }}
                </h1>

                @if(isset($article->contentBlocks['main_image']) && $article->contentBlocks['main_image'])
                    <x-drift::image
                            class="mt-16 rounded"
                            config="dashed"
                            :path="$article->contentBlocks['main_image']"
                            :alt="$article->name"
                            :manipulations="[
                            'widen' => 1000,
                        ]"
                    />
                @endif
            </header>

            <x-blocks :content="$article->content"></x-blocks>
        </x-container>
    </article>
</x-master>
