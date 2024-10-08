<article
        class="relative isolate flex flex-col justify-end overflow-hidden rounded-2xl bg-gray-900 px-8 pb-8 pt-80 sm:pt-48 lg:pt-80 transform hover:scale-110 transition-all ease-in-out duration-300">
    <x-drift::image
            class="absolute inset-0 -z-10 h-full w-full object-cover"
            config="dashed"
            :path="$article->contentBlocks['main_image']"
            :alt="$article->name"
            :manipulations="[
                'widen' => 800,
            ]"
    />
    <div class="absolute inset-0 -z-10 bg-gradient-to-tr from-primary-500 via-primary-500/60"></div>
    <div class="absolute inset-0 -z-10 rounded-2xl ring-1 ring-inset ring-black/10"></div>

    <div class="flex flex-wrap items-center gap-y-1 overflow-hidden text-sm leading-6 text-white">
        <p>{{ str($article->contentBlocks['excerpt'])->limit() }}</p>
    </div>
    <h3 class="mt-3 text-lg font-semibold leading-6 text-white">
        <a href="{{ $article->getUrl() }}">
            <span class="absolute inset-0"></span>
            {{ $article->name }}
        </a>
    </h3>
</article>