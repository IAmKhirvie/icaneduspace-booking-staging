<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-end gap-4 flex-wrap">
            <div>
                <p class="eyebrow mb-1">{{ __('Staff') }}</p>
                <h1 class="font-serif text-4xl text-brand-navy">{{ __('Notifications') }}</h1>
                <p class="text-brand-navy/60 mt-1">{{ __('Track customer and staff notification delivery from one place.') }}</p>
            </div>
            @if($counts['unread'] > 0)
                <form method="POST" action="{{ route('manage.notifications.read-all') }}">
                    @csrf
                    <button class="btn-ghost">{{ __('Mark all read') }}</button>
                </form>
            @endif
        </div>
    </x-slot>

    <div
        class="max-w-7xl mx-auto px-6 py-10 space-y-6"
        x-data="notificationCenterList()"
        x-init="restoreScroll()"
    >
        @if (session('flash'))
            <div class="border border-emerald-400 bg-emerald-50 px-5 py-3 text-sm text-emerald-700">{{ session('flash') }}</div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3">
            @foreach([
                ['label' => __('Unread'), 'count' => $counts['unread'], 'url' => route('manage.notifications.index', ['status' => 'unread']), 'tone' => 'status-pending', 'note' => __('Needs review')],
                ['label' => __('Delivery issues'), 'count' => $counts['issues'], 'url' => route('manage.notifications.index', ['status' => 'issues']), 'tone' => 'status-rejected', 'note' => __('Failed or skipped')],
                ['label' => __('Staff'), 'count' => $counts['staff'], 'url' => route('manage.notifications.index', ['audience' => 'staff']), 'tone' => 'status-approved', 'note' => __('Internal alerts')],
                ['label' => __('Customer'), 'count' => $counts['customer'], 'url' => route('manage.notifications.index', ['audience' => 'customer']), 'tone' => 'status-approved', 'note' => __('Customer emails')],
                ['label' => __('Today'), 'count' => $counts['today'], 'url' => route('manage.notifications.index'), 'tone' => 'status-pending', 'note' => __('New records')],
            ] as $card)
                <a href="{{ $card['url'] }}" class="border border-brand-navy/10 bg-white px-4 py-3 hover:bg-brand-navy/[0.02] transition-colors" @click="loading = true">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/45">{{ $card['label'] }}</p>
                            <p class="mt-1 text-xs text-brand-navy/50">{{ $card['note'] }}</p>
                        </div>
                        <span class="status-badge {{ $card['tone'] }}">{{ $card['count'] }}</span>
                    </div>
                </a>
            @endforeach
        </div>

        <form method="GET" class="card p-5 space-y-4" @submit="loading = true">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __('Search') }}</label>
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="{{ __('Recipient, subject, booking') }}">
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __('Status') }}</label>
                    <select name="status">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach([
                            'unread' => __('Unread'),
                            'read' => __('Read'),
                            'issues' => __('Delivery issues'),
                            'pending' => __('Pending'),
                            'sent' => __('Sent'),
                            'skipped' => __('Skipped'),
                            'failed' => __('Failed'),
                        ] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __('Audience') }}</label>
                    <select name="audience">
                        <option value="">{{ __('All audiences') }}</option>
                        <option value="staff" @selected($filters['audience'] === 'staff')>{{ __('Staff') }}</option>
                        <option value="customer" @selected($filters['audience'] === 'customer')>{{ __('Customer') }}</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __('Display') }}</label>
                    <select name="per_page">
                        @foreach([10, 20, 25, 50, 75, 100] as $size)
                            <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button class="btn-gold w-full">{{ __('Apply') }}</button>
                    <a href="{{ route('manage.notifications.index') }}" class="btn-ghost" @click="loading = true">{{ __('Reset') }}</a>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 border-t border-brand-navy/10 pt-4">
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __('Sort') }}</label>
                    <select name="sort">
                        <option value="newest" @selected($filters['sort'] === 'newest')>{{ __('Newest first') }}</option>
                        <option value="oldest" @selected($filters['sort'] === 'oldest')>{{ __('Oldest first') }}</option>
                    </select>
                </div>
            </div>
        </form>

        <div class="card relative overflow-hidden" :class="{ 'pointer-events-none': loading }" aria-live="polite">
            <div x-show="loading" x-cloak class="absolute inset-0 z-10 bg-white/85 backdrop-blur-[1px]">
                <div class="pt-12">
                    @for($i = 0; $i < min($perPage, 8); $i++)
                        <div class="grid grid-cols-7 gap-4 px-4 py-4 border-t border-brand-navy/10 first:border-t-0">
                            <div class="h-4 bg-brand-navy/10 animate-pulse"></div>
                            <div class="h-4 bg-brand-navy/10 animate-pulse"></div>
                            <div class="h-4 bg-brand-navy/10 animate-pulse"></div>
                            <div class="h-4 bg-brand-navy/10 animate-pulse"></div>
                            <div class="h-4 bg-brand-navy/10 animate-pulse"></div>
                            <div class="h-4 bg-brand-navy/10 animate-pulse"></div>
                            <div class="h-4 bg-brand-navy/10 animate-pulse"></div>
                        </div>
                    @endfor
                </div>
            </div>

            <div class="md:hidden divide-y divide-brand-navy/10">
                @forelse($notifications as $notification)
                    @php
                        $statusClass = match ($notification->status) {
                            \App\Models\BookingNotification::STATUS_SENT => 'status-approved',
                            \App\Models\BookingNotification::STATUS_FAILED => 'status-rejected',
                            \App\Models\BookingNotification::STATUS_SKIPPED => 'status-pending',
                            default => 'status-cancelled',
                        };
                        $type = class_basename($notification->notification_type);
                    @endphp
                    <article class="px-4 py-5 space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs text-brand-navy/45">#{{ $notification->id }}</p>
                                <h2 class="font-serif text-2xl text-brand-navy break-words">{{ $type }}</h2>
                                <p class="text-sm text-brand-navy/55 break-words">{{ $notification->recipient ?? __('No recipient') }}</p>
                            </div>
                            <span class="status-badge {{ $statusClass }} shrink-0">{{ __(ucfirst($notification->status)) }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-sm text-brand-navy/70">
                            <div>
                                <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/40">{{ __('Audience') }}</p>
                                <p>{{ __(ucfirst($notification->audience)) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/40">{{ __('Booking') }}</p>
                                <p>{{ $notification->booking ? '#'.$notification->booking->id : __('System') }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/40">{{ __('Created') }}</p>
                                <p>{{ optional($notification->created_at)->format('M d, Y H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/40">{{ __('Read') }}</p>
                                <p>{{ $notification->read_at ? $notification->read_at->format('M d, Y H:i') : __('Unread') }}</p>
                            </div>
                        </div>

                        <p class="text-sm text-brand-navy/65 break-words">{{ $notification->message }}</p>
                        @if($notification->error)
                            <p class="border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700 break-words">{{ $notification->error }}</p>
                        @endif

                        <div class="flex flex-wrap gap-2">
                            @if($notification->booking)
                                <a href="{{ route('bookings.show', ['booking' => $notification->booking, 'return' => $returnUrl]) }}" class="btn-ghost" @click="storeScroll(); loading = true">{{ __('View booking') }}</a>
                            @endif
                            @unless($notification->read_at)
                                <form method="POST" action="{{ route('manage.notifications.read', $notification) }}">
                                    @csrf
                                    <input type="hidden" name="return" value="{{ $returnUrl }}">
                                    <button class="btn-gold">{{ __('Mark read') }}</button>
                                </form>
                            @endunless
                        </div>
                    </article>
                @empty
                    <div class="px-4 py-12 text-center text-brand-navy/55">{{ __('No notifications found.') }}</div>
                @endforelse
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full min-w-[920px] text-xs">
                    <thead class="bg-brand-navy/5">
                        <tr class="text-left uppercase tracking-[0.12em] text-brand-navy/65">
                            <th class="px-4 py-3">{{ __('ID') }}</th>
                            <th class="px-4 py-3">{{ __('Notification') }}</th>
                            <th class="px-4 py-3">{{ __('Audience') }}</th>
                            <th class="px-4 py-3">{{ __('Booking') }}</th>
                            <th class="px-4 py-3">{{ __('Delivery') }}</th>
                            <th class="px-4 py-3">{{ __('Read') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notifications as $notification)
                            @php
                                $statusClass = match ($notification->status) {
                                    \App\Models\BookingNotification::STATUS_SENT => 'status-approved',
                                    \App\Models\BookingNotification::STATUS_FAILED => 'status-rejected',
                                    \App\Models\BookingNotification::STATUS_SKIPPED => 'status-pending',
                                    default => 'status-cancelled',
                                };
                                $type = class_basename($notification->notification_type);
                            @endphp
                            <tr class="border-t border-brand-navy/10 hover:bg-brand-navy/[0.02] align-top">
                                <td class="px-4 py-3 text-brand-navy/55">#{{ $notification->id }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-brand-navy">{{ $type }}</p>
                                    <p class="mt-1 text-brand-navy/55 break-words max-w-[280px]">{{ $notification->recipient ?? __('No recipient') }}</p>
                                    <p class="mt-1 text-brand-navy/45 break-words max-w-[280px]">{{ $notification->message }}</p>
                                    @if($notification->error)
                                        <p class="mt-2 border border-rose-200 bg-rose-50 px-2 py-1 text-rose-700 break-words max-w-[280px]">{{ $notification->error }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="status-badge {{ $notification->audience === 'staff' ? 'status-approved' : 'status-pending' }}">{{ __(ucfirst($notification->audience)) }}</span>
                                </td>
                                <td class="px-4 py-3 text-brand-navy/65">
                                    @if($notification->booking)
                                        <a href="{{ route('bookings.show', ['booking' => $notification->booking, 'return' => $returnUrl]) }}" class="underline decoration-brand-gold/50 underline-offset-4" @click="storeScroll(); loading = true">
                                            #{{ $notification->booking->id }}
                                        </a>
                                        <p class="mt-1">{{ $notification->booking->classroom?->name ?? __('No room') }}</p>
                                    @else
                                        {{ __('System') }}
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="status-badge {{ $statusClass }}">{{ __(ucfirst($notification->status)) }}</span>
                                    <p class="mt-2 text-brand-navy/45">{{ optional($notification->created_at)->format('M d, Y H:i') }}</p>
                                    @if($notification->sent_at)
                                        <p class="mt-1 text-brand-navy/45">{{ __('Sent') }} {{ $notification->sent_at->format('M d, Y H:i') }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="status-badge {{ $notification->read_at ? 'status-approved' : 'status-pending' }}">
                                        {{ $notification->read_at ? __('Read') : __('Unread') }}
                                    </span>
                                    @if($notification->read_at)
                                        <p class="mt-2 text-brand-navy/45">{{ $notification->read_at->format('M d, Y H:i') }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center justify-end gap-2">
                                        @if($notification->booking)
                                            <a href="{{ route('bookings.show', ['booking' => $notification->booking, 'return' => $returnUrl]) }}" class="btn-ghost" @click="storeScroll(); loading = true">{{ __('View') }}</a>
                                        @endif
                                        @unless($notification->read_at)
                                            <form method="POST" action="{{ route('manage.notifications.read', $notification) }}">
                                                @csrf
                                                <input type="hidden" name="return" value="{{ $returnUrl }}">
                                                <button class="btn-gold">{{ __('Mark read') }}</button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-12 text-center text-brand-navy/55">{{ __('No notifications found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <p class="text-sm text-brand-navy/55">
                {{ __('Showing') }} {{ $pagination['from'] }}-{{ $pagination['to'] }} {{ __('of') }} {{ $pagination['total'] }}
            </p>

            <nav class="flex flex-wrap items-center gap-2 text-sm" aria-label="Notification pages">
                @php
                    $pageUrls = collect($pagination['page_urls'])
                        ->filter(fn ($url, $page) => (int) $page >= 1 && (int) $page <= $pagination['last_page'])
                        ->sortKeys();
                    $lastPrintedPage = null;
                @endphp

                <a
                    href="{{ $pagination['first_url'] }}"
                    class="btn-ghost {{ $pagination['current_page'] === 1 ? 'pointer-events-none opacity-40' : '' }}"
                    @click="storeScroll(); loading = true"
                    aria-label="First notification page"
                >&lt;&lt;</a>
                <a
                    href="{{ $pagination['previous_url'] ?? '#' }}"
                    class="btn-ghost {{ $pagination['previous_url'] ? '' : 'pointer-events-none opacity-40' }}"
                    @click="storeScroll(); loading = true"
                    aria-label="Previous notification page"
                >&lt;</a>

                @foreach($pageUrls as $page => $url)
                    @if($lastPrintedPage !== null && ((int) $page - $lastPrintedPage) > 1)
                        <span class="px-2 text-brand-navy/35">...</span>
                    @endif

                    @if((int) $page === $pagination['current_page'])
                        <span class="btn-gold" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="btn-ghost" @click="storeScroll(); loading = true">{{ $page }}</a>
                    @endif

                    @php $lastPrintedPage = (int) $page; @endphp
                @endforeach

                <a
                    href="{{ $pagination['next_url'] ?? '#' }}"
                    class="btn-ghost {{ $pagination['next_url'] ? '' : 'pointer-events-none opacity-40' }}"
                    @click="storeScroll(); loading = true"
                    aria-label="Next notification page"
                >&gt;</a>
                <a
                    href="{{ $pagination['last_url'] }}"
                    class="btn-ghost {{ $pagination['current_page'] === $pagination['last_page'] ? 'pointer-events-none opacity-40' : '' }}"
                    @click="storeScroll(); loading = true"
                    aria-label="Last notification page"
                >&gt;&gt;</a>
            </nav>
        </div>
    </div>

    <script>
        function notificationCenterList() {
            return {
                loading: false,
                storageKey: 'ican:notification-center-scroll:' + window.location.pathname + window.location.search,
                storeScroll() {
                    window.sessionStorage.setItem(this.storageKey, String(window.scrollY));
                },
                restoreScroll() {
                    const value = window.sessionStorage.getItem(this.storageKey);

                    if (value !== null) {
                        requestAnimationFrame(() => window.scrollTo(0, Number(value) || 0));
                    }

                    window.addEventListener('beforeunload', () => this.storeScroll());
                },
            };
        }
    </script>
</x-app-layout>
