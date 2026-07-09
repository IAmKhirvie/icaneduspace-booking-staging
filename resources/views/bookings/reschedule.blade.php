<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-end gap-4 flex-wrap">
            <div>
                <p class="eyebrow mb-1">{{ __("Booking #") }}{{ $booking->id }}</p>
                <h1 class="font-serif text-4xl text-brand-navy">{{ __("Reschedule booking") }}</h1>
                <p class="text-brand-navy/60 mt-2 max-w-2xl">{{ __("Choose a new room, date, or time block.") }}</p>
            </div>
            <span class="status-badge status-{{ $booking->status }}">{{ $booking->statusLabel() }}</span>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto px-6 py-12 space-y-6">
        @if ($errors->any())
            <div class="border border-red-400 bg-red-50 px-5 py-4 text-sm text-red-600">
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                        <li>· {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card p-6">
            <p class="eyebrow mb-3">{{ __("Current schedule") }}</p>
            <div class="grid md:grid-cols-3 gap-4 text-sm text-brand-navy/75">
                <div>
                    <span class="block text-xs uppercase tracking-[0.18em] text-brand-navy/45 mb-1">{{ __("Room") }}</span>
                    {{ $booking->classroom?->name ?? '—' }}
                </div>
                <div>
                    <span class="block text-xs uppercase tracking-[0.18em] text-brand-navy/45 mb-1">{{ __("Date") }}</span>
                    {{ optional($booking->starts_at)->format('M d, Y') }}
                </div>
                <div>
                    <span class="block text-xs uppercase tracking-[0.18em] text-brand-navy/45 mb-1">{{ __("Time") }}</span>
                    {{ optional($booking->starts_at)->format('H:i') }}–{{ optional($booking->ends_at)->format('H:i') }}
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('bookings.reschedule', $booking) }}" class="card p-8 grid grid-cols-1 md:grid-cols-2 gap-5">
            @csrf

            <div class="md:col-span-2">
                <label class="block mb-2 text-xs uppercase tracking-[0.22em] text-brand-navy/60" for="classroom_id">{{ __("Room") }}</label>
                <select id="classroom_id" name="classroom_id" required>
                    @foreach($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" @selected((int) old('classroom_id', $booking->classroom_id) === $classroom->id)>
                            {{ $classroom->name }} · {{ $classroom->capacity }} {{ __("seats") }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-2 text-xs uppercase tracking-[0.22em] text-brand-navy/60" for="booking_date">{{ __("New date") }}</label>
                <input id="booking_date" name="booking_date" type="date" required min="{{ now()->toDateString() }}" value="{{ old('booking_date', optional($booking->booking_date)->toDateString()) }}">
            </div>

            <div>
                <label class="block mb-2 text-xs uppercase tracking-[0.22em] text-brand-navy/60" for="time_block">{{ __("New time") }}</label>
                <select id="time_block" name="time_block" required>
                    @foreach($timeBlocks as $block => [$start, $end])
                        <option value="{{ $block }}" @selected(old('time_block', $currentTimeBlock) === $block)>
                            {{ __(ucfirst($block)) }} · {{ $start }}–{{ $end }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block mb-2 text-xs uppercase tracking-[0.22em] text-brand-navy/60" for="reschedule_note">{{ __("Note") }}</label>
                <textarea id="reschedule_note" name="reschedule_note" rows="3" placeholder="{{ __('Reason or setup note for this schedule change') }}">{{ old('reschedule_note') }}</textarea>
            </div>

            @unless(auth()->user()->hasAnyRole(['super_admin', 'admin', 'staff']))
                <div class="md:col-span-2 border border-brand-gold/60 bg-brand-gold/10 px-5 py-4 text-sm text-brand-navy">
                    <p class="font-medium">{{ __("Staff review required") }}</p>
                    <p class="mt-1 text-brand-navy/65">{{ __("After you send this change, the booking returns to pending until staff confirms the new schedule.") }}</p>
                </div>
            @endunless

            <div class="md:col-span-2 flex flex-col md:flex-row justify-between items-center gap-4 pt-2">
                <a href="{{ route('bookings.show', $booking) }}" class="btn-ghost w-full md:w-auto text-center">{{ __("Back") }}</a>
                <button type="submit" class="btn-gold w-full md:w-auto">{{ __("Save new schedule") }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
