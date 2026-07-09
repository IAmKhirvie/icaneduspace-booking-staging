@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="px-6 py-4">
        <div class="text-lg font-medium text-brand-navy">
            {{ $title }}
        </div>

        <div class="mt-4 text-sm text-brand-navy/65">
            {{ $content }}
        </div>
    </div>

    <div class="flex flex-row justify-end px-6 py-4 bg-brand-soft text-end">
        {{ $footer }}
    </div>
</x-modal>
