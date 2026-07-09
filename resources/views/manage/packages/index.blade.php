<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-end gap-4 flex-wrap">
            <div>
                <p class="eyebrow mb-1">{{ __("Staff") }}</p>
                <h1 class="font-serif text-4xl text-brand-navy">{{ __("Service packages") }}</h1>
                <p class="text-brand-navy/60 mt-1">{{ __("Maintain the packages offered alongside room bookings.") }}</p>
            </div>
            <a href="{{ route('manage.packages.create') }}" class="btn-gold">+ {{ __("New package") }}</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-6">

        @if (session('flash'))
            <div class="border border-emerald-400 bg-emerald-50 px-5 py-3 text-sm text-emerald-700">{{ session('flash') }}</div>
        @endif

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($packages as $p)
                <div class="card p-5 flex flex-col">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-serif text-xl text-brand-navy">{{ $p->name }}</h3>
                        <span class="text-xs uppercase tracking-[0.18em] {{ $p->is_active ? 'text-emerald-700' : 'text-brand-navy/45' }}">
                            {{ $p->is_active ? __("Active") : __("Hidden") }}
                        </span>
                    </div>
                    <p class="text-sm text-brand-navy/65 line-clamp-3">{{ $p->description }}</p>
                    <p class="mt-3 font-serif text-2xl text-brand-gold">{{ \App\Support\Money::format($p->base_price) }}</p>
                    @if($p->duration_minutes)<p class="text-xs uppercase tracking-[0.18em] text-brand-navy/55">{{ $p->duration_minutes }} min</p>@endif
                    <div class="mt-auto pt-4 flex gap-2 justify-end items-center">
                        <a href="{{ route('manage.packages.edit', $p) }}" class="btn-ghost">{{ __("Edit") }}</a>
                        <form method="POST" action="{{ route('manage.packages.destroy', $p) }}" onsubmit="return confirm('Delete {{ $p->name }}?');" class="inline-flex">@csrf
                            <button type="submit" class="btn-ghost" style="border-color:#b91c1c;color:#b91c1c;">{{ __("Delete") }}</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div>{{ $packages->links() }}</div>
    </div>
</x-app-layout>
