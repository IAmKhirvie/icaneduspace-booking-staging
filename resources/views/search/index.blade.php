<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="eyebrow mb-1">{{ __('Search') }}</p>
            <h1 class="font-serif text-4xl text-brand-navy">{{ __('Global search') }}</h1>
            <p class="text-brand-navy/60 mt-1">{{ __('Bookings, rooms, packages, notifications, and users where permitted.') }}</p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-8">
        <form method="GET" action="{{ route('search.index') }}" class="card p-5">
            <div class="flex flex-col gap-3 md:flex-row">
                <div class="flex-1">
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block" for="search-page-q">{{ __('Search') }}</label>
                    <input id="search-page-q" type="text" name="q" value="{{ $query }}" placeholder="{{ __('Search bookings, rooms, packages') }}" autofocus>
                </div>
                <div class="flex items-end">
                    <button class="btn-gold w-full md:w-auto">{{ __('Search') }}</button>
                </div>
            </div>
        </form>

        @php
            $totalResults = collect($results)->sum(fn ($items) => $items->count());
        @endphp

        @if($query === '')
            <div class="border border-brand-navy/10 bg-white px-5 py-6 text-brand-navy/60">{{ __('Enter a search term to begin.') }}</div>
        @elseif($totalResults === 0)
            <div class="border border-brand-navy/10 bg-white px-5 py-6 text-brand-navy/60">{{ __('No results found.') }}</div>
        @endif

        @if($correction)
            <div class="border border-brand-gold/35 bg-brand-gold/10 px-5 py-4 text-sm text-brand-navy/70" data-search-correction>
                {{ __('Did you mean') }}
                <a href="{{ $correction['url'] }}" class="font-semibold text-brand-navy hover:text-brand-gold">{{ $correction['query'] }}</a>?
                <span class="ml-2 text-brand-navy/45">{{ __('Closest match:') }} {{ $correction['label'] }}</span>
            </div>
        @endif

        @if($results['bookings']->isNotEmpty())
            <section class="space-y-3" data-search-section="bookings">
                <div class="flex items-center justify-between">
                    <h2 class="font-serif text-2xl text-brand-navy">{{ __('Bookings') }}</h2>
                    <span class="status-badge status-pending">{{ $results['bookings']->count() }}</span>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                    @foreach($results['bookings'] as $booking)
                        <a href="{{ route('bookings.show', ['booking' => $booking, 'return' => request()->fullUrl()]) }}" class="border border-brand-navy/10 bg-white px-5 py-4 hover:bg-brand-navy/[0.02] transition-colors">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs text-brand-navy/45">#{{ $booking->id }}</p>
                                    <p class="font-medium text-brand-navy wrap-anywhere">{{ $booking->customer_name }}</p>
                                    <p class="mt-1 text-sm text-brand-navy/55 wrap-anywhere">{{ $booking->purpose }}</p>
                                    <p class="mt-2 text-xs text-brand-navy/45">{{ $booking->classroom?->name ?? __('No room') }} · {{ optional($booking->starts_at)->format('M d, Y H:i') }}</p>
                                </div>
                                <span class="status-badge {{ $booking->workflowBadgeClass() }} shrink-0">{{ $booking->workflowStageLabel() }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($results['rooms']->isNotEmpty())
            <section class="space-y-3" data-search-section="rooms">
                <div class="flex items-center justify-between">
                    <h2 class="font-serif text-2xl text-brand-navy">{{ __('Rooms') }}</h2>
                    <span class="status-badge status-approved">{{ $results['rooms']->count() }}</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                    @foreach($results['rooms'] as $room)
                        <a href="{{ route('rooms.show', $room) }}" class="border border-brand-navy/10 bg-white px-5 py-4 hover:bg-brand-navy/[0.02] transition-colors">
                            <p class="font-medium text-brand-navy wrap-anywhere">{{ $room->name }}</p>
                            <p class="mt-1 text-sm text-brand-navy/55">{{ collect([$room->location, $room->floor, $room->room_number])->filter()->implode(' · ') }}</p>
                            <p class="mt-2 text-xs text-brand-navy/45">{{ __('Capacity') }} {{ $room->capacity }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($results['packages']->isNotEmpty())
            <section class="space-y-3" data-search-section="packages">
                <div class="flex items-center justify-between">
                    <h2 class="font-serif text-2xl text-brand-navy">{{ __('Packages') }}</h2>
                    <span class="status-badge status-approved">{{ $results['packages']->count() }}</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                    @foreach($results['packages'] as $package)
                        <a href="{{ url('/#packages') }}" class="border border-brand-navy/10 bg-white px-5 py-4 hover:bg-brand-navy/[0.02] transition-colors">
                            <p class="font-medium text-brand-navy wrap-anywhere">{{ $package->name }}</p>
                            <p class="mt-1 text-sm text-brand-navy/55 wrap-anywhere">{{ $package->description }}</p>
                            <p class="mt-2 text-xs text-brand-navy/45">{{ \App\Support\Money::format($package->base_price) }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($canManage && $results['notifications']->isNotEmpty())
            <section class="space-y-3" data-search-section="notifications">
                <div class="flex items-center justify-between">
                    <h2 class="font-serif text-2xl text-brand-navy">{{ __('Notifications') }}</h2>
                    <span class="status-badge status-pending">{{ $results['notifications']->count() }}</span>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                    @foreach($results['notifications'] as $notification)
                        <a href="{{ route('manage.notifications.index', ['q' => $query]) }}" class="border border-brand-navy/10 bg-white px-5 py-4 hover:bg-brand-navy/[0.02] transition-colors">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs text-brand-navy/45">#{{ $notification->id }}</p>
                                    <p class="font-medium text-brand-navy wrap-anywhere">{{ class_basename($notification->notification_type) }}</p>
                                    <p class="mt-1 text-sm text-brand-navy/55 wrap-anywhere">{{ $notification->message }}</p>
                                    <p class="mt-2 text-xs text-brand-navy/45">{{ $notification->recipient ?? __('No recipient') }}</p>
                                </div>
                                <span class="status-badge {{ $notification->read_at ? 'status-approved' : 'status-pending' }} shrink-0">{{ $notification->read_at ? __('Read') : __('Unread') }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($results['users']->isNotEmpty())
            <section class="space-y-3" data-search-section="users">
                <div class="flex items-center justify-between">
                    <h2 class="font-serif text-2xl text-brand-navy">{{ __('Users') }}</h2>
                    <span class="status-badge status-completed">{{ $results['users']->count() }}</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                    @foreach($results['users'] as $resultUser)
                        <a href="{{ route('manage.users.edit', $resultUser) }}" class="border border-brand-navy/10 bg-white px-5 py-4 hover:bg-brand-navy/[0.02] transition-colors">
                            <p class="font-medium text-brand-navy wrap-anywhere">{{ $resultUser->name }}</p>
                            <p class="mt-1 text-sm text-brand-navy/55 wrap-anywhere">{{ $resultUser->email }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
