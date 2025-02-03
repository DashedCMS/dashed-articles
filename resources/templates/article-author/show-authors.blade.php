<div>
    <x-container>
        @if($blockData['title'] ?? false)
            <h2 class="text-balance text-4xl font-semibold tracking-tight text-primary sm:text-5xl">
                {{ $blockData['title'] }}
            </h2>
        @endif
        @if($blockData['subtitle'] ?? false)
            <p class="mt-4 text-lg/8 text-gray-400">
                {{ $blockData['subtitle'] }}
            </p>
        @endif

        <div
            class="mx-auto mt-8 grid max-w-2xl auto-rows-fr grid-cols-2 gap-8 sm:mt-12 lg:mx-0 lg:max-w-none lg:grid-cols-4">
            @foreach($authors as $author)
                <x-article-author :author="$author"/>
            @endforeach
        </div>
        <div class="mt-4">
            {{ $authors->links('dashed.partials.pagination') }}
        </div>
    </x-container>
</div>
