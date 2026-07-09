<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-end gap-4 flex-wrap">
            <div>
                <p class="eyebrow mb-1">{{ __("Staff") }}</p>
                <h1 class="font-serif text-4xl text-brand-navy">{{ __("Classrooms") }}</h1>
                <p class="text-brand-navy/60 mt-1">{{ __("Manage rooms, capacity, rates, and images.") }}</p>
            </div>
            <a href="{{ route('manage.classrooms.create') }}" class="btn-gold">+ {{ __("New classroom") }}</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-6">

        @if (session('flash'))
            <div class="border border-emerald-400 bg-emerald-50 px-5 py-3 text-sm text-emerald-700">{{ session('flash') }}</div>
        @endif

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($classrooms as $room)
                <div class="card overflow-hidden">
                    <div class="h-44 bg-cover bg-center bg-brand-navy/10" style="background-image:url('{{ $room->hero_image }}')"></div>
                    <div class="p-5 space-y-2">
                        <div class="flex justify-between items-start">
                            <h3 class="font-serif text-xl text-brand-navy">{{ $room->name }}</h3>
                            <span class="text-xs uppercase tracking-[0.18em] {{ $room->is_active ? 'text-emerald-700' : 'text-brand-navy/45' }}">
                                {{ $room->is_active ? __("Active") : __("Hidden") }}
                            </span>
                        </div>
                        <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/55">
                            {{ collect([$room->location, $room->floor, $room->room_number ? __('Room').' '.$room->room_number : null])->filter()->implode(' · ') }}
                        </p>
                        <p class="text-sm text-brand-navy/65 line-clamp-2">{{ $room->description }}</p>
                        <div class="flex justify-between items-end pt-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/45">{{ __("Capacity / Rate") }}</p>
                                <p class="text-brand-navy font-medium">{{ $room->capacity }} · {{ \App\Support\Money::format($room->hourly_rate) }}/hr</p>
                            </div>
                            <div class="flex gap-2 items-center">
                                <a href="{{ route('manage.classrooms.edit', $room) }}" class="btn-ghost">{{ __("Edit") }}</a>
                                <form method="POST" action="{{ route('manage.classrooms.destroy', $room) }}" onsubmit="return confirm('Delete {{ $room->name }}?');" class="inline-flex">
                                    @csrf
                                    <button type="submit" class="btn-ghost" style="border-color:#b91c1c;color:#b91c1c;">{{ __("Delete") }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div>{{ $classrooms->links() }}</div>
    </div>
</x-app-layout>
