<x-app-layout>
    <x-slot name="header">
        <p class="eyebrow mb-1">{{ $package->exists ? 'Edit' : 'New' }}</p>
        <h1 class="font-serif text-4xl text-brand-navy">{{ $package->exists ? $package->name : 'New package' }}</h1>
    </x-slot>

    <div class="max-w-3xl mx-auto px-6 py-10 space-y-6">

        @if ($errors->any())
            <div class="border border-red-300 bg-red-50 px-5 py-3 text-sm text-red-700">
                <ul class="space-y-1">@foreach($errors->all() as $e)<li>· {{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ $package->exists ? route('manage.packages.update', $package) : route('manage.packages.store') }}"
              class="card p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
            @csrf
            <div class="md:col-span-2">
                <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Name") }}</label>
                <input name="name" required value="{{ old('name', $package->name) }}">
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Slug") }}</label>
                <input name="slug" required value="{{ old('slug', $package->slug) }}">
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Base price (PHP)") }}</label>
                <input name="base_price" type="number" min="0" required value="{{ old('base_price', $package->base_price ?? 0) }}">
            </div>
            <div class="md:col-span-2">
                <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Description") }}</label>
                <textarea name="description" rows="3">{{ old('description', $package->description) }}</textarea>
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Duration (minutes, optional)") }}</label>
                <input name="duration_minutes" type="number" min="0" max="1440" value="{{ old('duration_minutes', $package->duration_minutes) }}">
            </div>
            <div class="md:col-span-2">
                <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Included services (comma or new-line)") }}</label>
                <textarea name="included_services" rows="3">{{ old('included_services', is_array($package->included_services) ? implode("\n", $package->included_services) : '') }}</textarea>
            </div>
            <label class="md:col-span-2 flex items-center gap-2 text-sm normal-case tracking-normal">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $package->is_active)) class="!w-4 !h-4 accent-brand-gold">
                <span class="text-brand-navy/75">{{ __("Active (visible to customers)") }}</span>
            </label>

            <div class="md:col-span-2 flex gap-3 justify-between pt-3">
                <a href="{{ route('manage.packages.index') }}" class="btn-ghost">← {{ __("Cancel") }}</a>
                <button class="btn-gold">{{ $package->exists ? 'Save changes' : 'Create package' }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
