<x-site-layout title="Our Rooms">
    <section class="max-w-7xl mx-auto px-6 pt-20 pb-12">
        <p class="eyebrow mb-3">{{ __("Spaces") }}</p>
        <h1 class="font-serif text-5xl md:text-6xl text-white mb-4">{{ __("Pick your room.") }}</h1>
        <p class="text-white/65 max-w-2xl">Each classroom is set up for a different style of session — from AI workshops to small-group mentoring. Browse, then send a booking request.</p>
    </section>

    <section class="max-w-7xl mx-auto px-6 pb-20 grid md:grid-cols-3 gap-6">
        @foreach($classrooms as $classroom)
            <a href="{{ route('rooms.show', $classroom) }}" class="room-card block">
                <div class="img-bg" style="background-image:url('{{ $classroom->hero_image }}')"></div>
                <div class="scrim"></div>
                <div class="relative z-10 p-7 h-full flex flex-col justify-end min-h-[420px]">
                    <p class="eyebrow mb-2">{{ $classroom->location }}</p>
                    <h2 class="font-serif text-3xl text-white mb-2">{{ $classroom->name }}</h2>
                    <p class="text-white/65 text-sm mb-4 line-clamp-2">{{ $classroom->description }}</p>
                    <div class="flex justify-between items-center text-xs uppercase tracking-[0.22em] text-white/55">
                        <span>{{ $classroom->capacity }} seats</span>
                        <span class="text-brand-gold">{{ \App\Support\Money::format($classroom->hourly_rate) }} / hr</span>
                    </div>
                </div>
            </a>
        @endforeach
    </section>
</x-site-layout>
