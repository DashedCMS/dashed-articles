<div class="bg-white @if($blockData['top_margin'] ?? false) pt-16 sm:pt-24 @endif @if($blockData['bottom_margin'] ?? false) pb-16 sm:pb-24 @endif">
    <x-container :show="$blockData['in_container'] ?? false">
        <div class="mx-auto max-w-2xl lg:mx-0">
            <h2 class="text-pretty text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl">
                {{ $blockData['title'] }}
            </h2>

            @if($blockData['subtitle'] ?? false)
                <p class="mt-6 text-lg/8 text-gray-600">
                    {!! $blockData['subtitle'] !!}
                </p>
            @endif
        </div>
        <ul role="list"
            class="mx-auto mt-20 grid max-w-2xl grid-cols-2 gap-x-8 gap-y-16 text-center sm:grid-cols-3 md:grid-cols-4 lg:mx-0 lg:max-w-none lg:grid-cols-5 xl:grid-cols-6">
            @foreach($authors as $author)
                <a href="{{ $author->url }}" class="hover trans transform hover:-translate-y-2 duration-300 ease-in-out">
                    @if($author->image)
                        <x-dashed-files::image
                            :mediaId="$author->image"
                            class="mx-auto size-24 rounded-full"
                        />
                    @endif
                    <h3 class="mt-6 text-base/7 font-semibold tracking-tight text-gray-900">{{ $author->name }}</h3>
                    <p class="text-sm/6 text-gray-600">{{ Translation::get('amount-of-blogs-count', 'authors', ':count: blogs geschreven', 'text', [
                        'count' => $author->articles->count(),
                    ]) }}</p>
                </a>
            @endforeach
        </ul>
    </x-container>
</div>
