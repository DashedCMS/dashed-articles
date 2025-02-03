<div>
    <x-container>
        <div class="mx-auto mt-16 grid max-w-2xl auto-rows-fr grid-cols-1 gap-8 sm:mt-20 lg:mx-0 lg:max-w-none lg:grid-cols-3">
            @foreach($articles as $article)
                <x-article :article="$article"/>
            @endforeach
        </div>
        <div class="mt-4">
            {{ $articles->links('dashed.partials.pagination') }}
        </div>
    </x-container>
</div>
