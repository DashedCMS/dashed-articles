<x-master>
    <livewire:articles.show-articles
        :authorId="$articleAuthor->id"
    />

    <x-blocks :content="$articleAuthor->content"></x-blocks>
</x-master>
