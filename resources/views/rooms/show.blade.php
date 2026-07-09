<x-site-layout :title="$classroom->name">
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image:url('{{ $classroom->hero_image }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-brand-dark/40 via-brand-dark/70 to-brand-dark"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-6 pt-32 pb-24">
            <p class="eyebrow mb-3">{{ collect([$classroom->location, $classroom->floor, $classroom->room_number ? __('Room').' '.$classroom->room_number : null])->filter()->implode(' · ') }}</p>
            <h1 class="font-serif text-5xl md:text-7xl text-white mb-5">{{ $classroom->name }}</h1>
            <p class="text-white/75 max-w-2xl text-lg font-light leading-relaxed">{{ $classroom->description }}</p>
            <div class="mt-8 flex flex-wrap gap-6 items-center">
                <a href="{{ route('home') }}#contact?room={{ $classroom->id }}" class="btn-gold">{{ __("Book this room") }}</a>
                <span class="text-xs uppercase tracking-[0.22em] text-white/55">{{ $classroom->capacity }} seats · {{ \App\Support\Money::format($classroom->hourly_rate) }} / hr</span>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-6 py-16 grid md:grid-cols-3 gap-6">
        @if(!empty($classroom->amenities))
            <div class="glass-panel p-8 md:col-span-2">
                <p class="eyebrow mb-4">{{ __("What's included") }}</p>
                <ul class="grid sm:grid-cols-2 gap-3">
                    @foreach($classroom->amenities as $amenity)
                        <li class="text-white/75 flex items-center gap-3">
                            <span class="w-1.5 h-1.5 bg-brand-gold inline-block"></span>
                            {{ $amenity }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="glass-panel p-8 flex flex-col">
            <p class="eyebrow mb-2">{{ __("Capacity") }}</p>
            <p class="font-serif text-4xl text-white mb-6">{{ $classroom->capacity }}</p>
            <p class="eyebrow mb-2">{{ __("Rate") }}</p>
            <p class="font-serif text-3xl text-brand-gold mb-6">{{ \App\Support\Money::format($classroom->hourly_rate) }}<span class="text-base text-white/55 ml-2">/ hour</span></p>
            @if($classroom->floor || $classroom->room_number)
                <p class="eyebrow mb-2">{{ __("Which room") }}</p>
                <p class="text-white/70 mb-6">{{ collect([$classroom->floor, $classroom->room_number ? __('Room').' '.$classroom->room_number : null])->filter()->implode(' · ') }}</p>
            @endif
            <a href="{{ route('home') }}#contact" class="btn-gold mt-auto">{{ __("Request booking") }}</a>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-6 pb-16">
        <p class="eyebrow mb-5">{{ __("Gallery") }}</p>
        <x-room-gallery-slider :room="$classroom" dark />
    </section>

    @if($classroom->map_embed_url)
        <section class="max-w-7xl mx-auto px-6 pb-20">
            <div class="flex justify-between items-end mb-5 flex-wrap gap-3">
                <div>
                    <p class="eyebrow mb-2">{{ __("Find us") }}</p>
                    <h3 class="font-serif text-3xl text-white">{{ $classroom->address }}</h3>
                </div>
                <a href="https://www.google.com/maps?q={{ urlencode($classroom->address) }}" target="_blank" rel="noopener" class="btn-ghost">{{ __("Open in maps") }} ↗</a>
            </div>
            @if($classroom->arrival_instructions)
                <p class="mb-5 text-white/65 max-w-3xl whitespace-pre-line">{{ $classroom->arrival_instructions }}</p>
            @endif
            <div class="glass-panel overflow-hidden">
                <iframe src="{{ $classroom->map_embed_url }}" width="100%" height="420" style="border:0;filter:invert(0.92) hue-rotate(180deg) saturate(0.8);" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </section>
    @endif

    <section class="max-w-7xl mx-auto px-6 pb-20">
        <p class="eyebrow mb-5">{{ __("Pair with a package") }}</p>
        <div class="grid md:grid-cols-3 gap-5">
            @foreach($packages as $package)
                <div class="glass-panel p-6">
                    <h4 class="font-serif text-2xl text-white mb-2">{{ $package->name }}</h4>
                    <p class="text-white/65 text-sm mb-4">{{ $package->description }}</p>
                    <p class="text-brand-gold font-serif text-2xl">{{ \App\Support\Money::format($package->base_price) }}</p>
                </div>
            @endforeach
        </div>
    </section>
</x-site-layout>
