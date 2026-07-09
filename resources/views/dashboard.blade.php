<x-app-layout>
    <x-slot name="header">
        <p class="eyebrow mb-1">{{ __('Welcome') }}</p>
        <h1 class="font-serif text-4xl text-brand-navy">{{ __('Hello, :name.', ['name' => auth()->user()->name]) }}</h1>
        <p class="text-brand-navy/60 mt-2 max-w-2xl">{{ __('Your bookings, your rooms, your shortcuts.') }}</p>
    </x-slot>

    <div class="max-w-7xl mx-auto px-6 py-12 space-y-12">

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="card p-6">
                <p class="eyebrow mb-2">{{ __('Pending') }}</p>
                <p class="font-serif text-4xl text-brand-gold">{{ $counts['pending'] }}</p>
            </div>
            <div class="card p-6">
                <p class="eyebrow mb-2">{{ __('Booked') }}</p>
                <p class="font-serif text-4xl text-emerald-700">{{ $counts['approved'] }}</p>
            </div>
            <div class="card p-6">
                <p class="eyebrow mb-2">{{ __('Past') }}</p>
                <p class="font-serif text-4xl text-brand-navy/55">{{ $counts['past'] }}</p>
            </div>
            <a href="{{ route('bookings.create') }}" class="card p-6 flex items-center justify-center hover:-translate-y-1 transition-transform">
                <span class="btn-gold">+ {{ __('New booking') }}</span>
            </a>
        </div>

        {{-- Upcoming --}}
        <div>
            <div class="flex justify-between items-end mb-5 flex-wrap gap-3">
                <div>
                    <p class="eyebrow mb-1">{{ __('Upcoming') }}</p>
                    <h2 class="font-serif text-3xl text-brand-navy">{{ __('Next sessions') }}</h2>
                </div>
                <a href="{{ route('bookings.index') }}" class="link-gold text-xs uppercase tracking-[0.22em]">{{ __('View all') }} →</a>
            </div>

            @forelse($upcoming as $booking)
                <a href="{{ route('bookings.show', $booking) }}" class="card p-6 mb-3 flex flex-wrap justify-between gap-4 hover:-translate-y-0.5 transition-transform block">
                    <div class="flex gap-5 items-center">
                        @if($booking->classroom)
                            <div class="w-20 h-20 bg-cover bg-center shrink-0" style="background-image:url('{{ $booking->classroom->hero_image }}')"></div>
                        @endif
                        <div>
                            <p class="eyebrow mb-1">#{{ $booking->id }} · {{ optional($booking->starts_at)->format('M d, Y') }}</p>
                            <h3 class="font-serif text-2xl text-brand-navy mb-1">{{ $booking->purpose }}</h3>
                            <p class="text-brand-navy/60 text-sm">
                                {{ $booking->classroom?->name ?? '—' }} ·
                                {{ optional($booking->starts_at)->format('H:i') }}–{{ optional($booking->ends_at)->format('H:i') }}
                            </p>
                        </div>
                    </div>
                    <span class="status-badge status-{{ $booking->status }} self-start">{{ $booking->statusLabel() }}</span>
                </a>
            @empty
                <div class="card p-10 text-center">
                    <p class="text-brand-navy/65 mb-4">{{ __('No upcoming sessions yet.') }}</p>
                    <a href="{{ route('bookings.create') }}" class="btn-gold">{{ __('Book a room') }}</a>
                </div>
            @endforelse
        </div>

        {{-- Featured rooms --}}
        <div>
            <div class="flex justify-between items-end mb-5 flex-wrap gap-3">
                <div>
                    <p class="eyebrow mb-1">{{ __('Rooms') }}</p>
                    <h2 class="font-serif text-3xl text-brand-navy">{{ __('Browse spaces') }}</h2>
                </div>
                <a href="{{ route('rooms.index') }}" class="link-gold text-xs uppercase tracking-[0.22em]">{{ __('All rooms') }} →</a>
            </div>
            <div class="grid md:grid-cols-3 gap-5">
                @foreach($rooms as $room)
                    <a href="{{ route('rooms.show', $room) }}" class="room-card block">
                        <div class="img-bg" style="background-image:url('{{ $room->hero_image }}')"></div>
                        <div class="scrim"></div>
                        <div class="relative z-10 p-6 h-full min-h-[280px] flex flex-col justify-end">
                            <p class="eyebrow mb-1">{{ $room->location }}</p>
                            <h3 class="font-serif text-2xl text-white mb-1">{{ $room->name }}</h3>
                            <p class="text-white/70 text-xs uppercase tracking-[0.18em]">{{ $room->capacity }} {{ __('seats') }} · {{ \App\Support\Money::format($room->hourly_rate) }}/hr</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
