<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-end gap-4 flex-wrap">
            <div>
                <p class="eyebrow mb-1">{{ __("Staff") }}</p>
                <h1 class="font-serif text-4xl text-brand-navy">{{ __("Booking Management") }}</h1>
                <p class="text-brand-navy/60 mt-1">{{ __("Review requests, payments, rooms, schedule, cancellation, and special cases.") }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if($canManageBookingSettings)
	                    <button
	                        type="button"
	                        class="btn-ghost inline-flex items-center justify-center"
	                        style="width: 2.5rem; height: 2.5rem; padding: 0;"
	                        aria-label="{{ __('Booking settings') }}"
	                        @click="$dispatch('toggle-booking-settings')"
	                        x-data
	                    >
		                        <svg width="18" height="18" style="width: 18px; height: 18px; flex: 0 0 18px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
		                            <path d="M4 6h16" />
		                            <path d="M4 12h16" />
		                            <path d="M4 18h16" />
		                            <path d="M9 4v4" />
		                            <path d="M15 10v4" />
		                            <path d="M11 16v4" />
		                        </svg>
                    </button>
                @endif
                <a href="{{ route('bookings.create') }}" class="btn-gold">+ {{ __("New booking") }}</a>
            </div>
        </div>
    </x-slot>

    <div
	        class="max-w-7xl mx-auto px-6 py-10 space-y-6"
	        x-data="bookingManagementList()"
	        x-init="restoreScroll()"
	        @toggle-booking-settings.window="settingsOpen = ! settingsOpen"
	        @action-tooltip-show="showTooltip($event.detail.text, $event.detail.x, $event.detail.y)"
	        @action-tooltip-move="moveTooltip($event.detail.x, $event.detail.y)"
	        @action-tooltip-hide="hideTooltip()"
	    >
	        <div
	            x-ref="actionTooltip"
	            x-show="tooltip.visible"
	            x-cloak
	            class="action-floating-tooltip"
	            :style="tooltip.style"
	            x-text="tooltip.text"
	        ></div>

	        @if (session('flash'))
            <div class="border border-emerald-400 bg-emerald-50 px-5 py-3 text-sm text-emerald-700">{{ session('flash') }}</div>
        @endif

        @if($canManageBookingSettings)
            <form
                method="POST"
                action="{{ route('manage.bookings.settings') }}"
                class="card p-5 grid grid-cols-1 md:grid-cols-4 gap-4"
                x-show="settingsOpen"
                x-cloak
                x-transition
            >
                @csrf
	                <div>
	                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Reservation fee %") }}</label>
	                    <input type="number" name="reservation_fee_percent" min="0" max="{{ \App\Models\BookingSetting::RESERVATION_FEE_MAX_PERCENT }}" step="0.01" value="{{ $bookingSettings['reservation_fee_percent'] }}">
	                    <p class="mt-1 text-xs text-brand-navy/45">{{ __("Maximum allowed: :percent%.", ['percent' => number_format(\App\Models\BookingSetting::RESERVATION_FEE_MAX_PERCENT, 0)]) }}</p>
	                </div>
	                <div>
	                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Special discount %") }}</label>
	                    <input type="number" name="special_discount_percent" min="0" max="100" step="0.01" value="{{ $bookingSettings['special_discount_percent'] }}">
	                    <p class="mt-1 text-xs text-brand-navy/45">{{ __("Applied before reservation fee.") }}</p>
	                </div>
	                <div class="md:col-span-2">
	                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Payment instructions") }}</label>
	                    <textarea name="payment_instructions" rows="2">{{ $bookingSettings['payment_instructions'] }}</textarea>
	                </div>
                <div class="md:col-span-4">
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Default arrival instructions") }}</label>
                    <textarea name="arrival_instructions" rows="2">{{ $bookingSettings['arrival_instructions'] }}</textarea>
                </div>
                <div class="md:col-span-4 flex justify-end">
                    <button class="btn-gold">{{ __("Save booking settings") }}</button>
                </div>
            </form>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3">
            @foreach($alerts as $alert)
                <a href="{{ $alert['url'] }}" class="border border-brand-navy/10 bg-white px-4 py-3 hover:bg-brand-navy/[0.02] transition-colors" @click="loading = true">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/45">{{ __($alert['label']) }}</p>
                            <p class="mt-1 text-xs text-brand-navy/50">{{ __($alert['note']) }}</p>
                        </div>
                        <span class="status-badge {{ $alert['tone'] }}">{{ $alert['count'] }}</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs uppercase tracking-[0.18em] text-brand-navy/45">{{ __("Quick filters") }}</span>
            @foreach([
                'pending' => __('Pending'),
                'awaiting_payment' => __('Awaiting payment'),
                'paid' => __('Paid'),
                'today' => __('Today'),
                'this_week' => __('This week'),
                'cancelled' => __('Cancelled'),
                'special_cases' => __('Special cases'),
                'notification_failures' => __('Notification issues'),
            ] as $quickValue => $quickLabel)
                <a
                    href="{{ route('manage.bookings.index', array_filter(['quick' => $quickValue, 'per_page' => $perPage], fn ($value) => $value !== null && $value !== '')) }}"
                    class="{{ ($filters['quick'] ?? null) === $quickValue ? 'btn-gold' : 'btn-ghost' }}"
                    @click="loading = true"
                >{{ $quickLabel }}</a>
            @endforeach
        </div>

        <form method="GET" class="card p-5 space-y-4" @submit="loading = true">
            @if($filters['quick'])
                <input type="hidden" name="quick" value="{{ $filters['quick'] }}">
            @endif
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Search") }}</label>
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="{{ __("Customer, contact, purpose, notes") }}">
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Status") }}</label>
                    <select name="status">
                        <option value="">{{ __("All") }}</option>
                        @foreach(\App\Services\BookingService::STATUS_LABELS as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status']===$value)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Room") }}</label>
                    <select name="classroom_id">
                        <option value="">{{ __("All rooms") }}</option>
                        @foreach($classrooms as $c)
                            <option value="{{ $c->id }}" @selected((int)$filters['classroom_id']===$c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Display") }}</label>
                    <select name="per_page">
                        @foreach([10, 20, 25, 50, 75, 100] as $size)
                            <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button class="btn-gold w-full">{{ __("Apply") }}</button>
                    <a href="{{ route('manage.bookings.index') }}" class="btn-ghost" @click="loading = true">{{ __("Reset") }}</a>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 border-t border-brand-navy/10 pt-4">
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Reservation fee") }}</label>
                    <select name="fee_status">
                        <option value="">{{ __("All fee statuses") }}</option>
                        <option value="paid" @selected($filters['fee_status'] === 'paid')>{{ __("Paid") }}</option>
                        <option value="unpaid" @selected($filters['fee_status'] === 'unpaid')>{{ __("Unpaid") }}</option>
                        <option value="not_required" @selected($filters['fee_status'] === 'not_required')>{{ __("Not required") }}</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Special cases") }}</label>
                    <select name="special_case">
                        <option value="">{{ __("All bookings") }}</option>
                        <option value="open" @selected($filters['special_case'] === 'open')>{{ __("Open special cases") }}</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Sort") }}</label>
                    <select name="sort">
                        <option value="newest" @selected($filters['sort'] === 'newest')>{{ __("Newest first") }}</option>
                        <option value="oldest" @selected($filters['sort'] === 'oldest')>{{ __("Oldest first") }}</option>
                    </select>
                </div>
            </div>
        </form>

        <div class="card relative" :class="{ 'pointer-events-none': loading }" aria-live="polite">
            <div x-show="loading" x-cloak class="absolute inset-0 z-10 bg-white/85 backdrop-blur-[1px]">
                <div class="pt-12">
                    @for($i = 0; $i < min($perPage, 8); $i++)
                        <div class="grid grid-cols-8 gap-4 px-4 py-4 border-t border-brand-navy/10 first:border-t-0">
                            <div class="h-4 bg-brand-navy/10 animate-pulse"></div>
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

            <div class="lg:hidden divide-y divide-brand-navy/10">
                @forelse($bookings as $b)
                    <article class="px-4 py-5 space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs text-brand-navy/45">#{{ $b->id }}</p>
                                <h2 class="font-serif text-2xl text-brand-navy break-words">{{ $b->customer_name }}</h2>
                                <p class="text-sm text-brand-navy/55 break-words">{{ $b->contact }}</p>
                            </div>
                            <span class="status-badge {{ $b->workflowBadgeClass() }} shrink-0">{{ $b->workflowStageLabel() }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-sm text-brand-navy/70">
                            <div>
                                <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/40">{{ __("When") }}</p>
                                <p>{{ optional($b->starts_at)->format('M d, Y') }}</p>
                                <p>{{ optional($b->starts_at)->format('H:i') }}-{{ optional($b->ends_at)->format('H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/40">{{ __("Where") }}</p>
                                <p>{{ $b->classroom?->name ?? __('To be confirmed') }}</p>
                                <p>{{ collect([$b->classroom?->floor, $b->classroom?->room_number ? __('Room').' '.$b->classroom->room_number : null])->filter()->implode(' · ') }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/40">{{ __("What") }}</p>
                                <p>{{ $b->servicePackage?->name ?? __('No package') }}</p>
                                <p>{{ $b->purpose }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/40">{{ __("Payment") }}</p>
                                <p>{{ $b->estimated_price !== null ? \App\Support\Money::format($b->estimated_price) : '—' }}</p>
                                <p>{{ $b->paymentScopeLabel() }} · {{ $b->selectedPaymentStatusLabel() }}</p>
                            </div>
                        </div>

                        @if($b->workflowWarnings())
                            <div class="border border-amber-300 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                @foreach($b->workflowWarnings() as $warning)
                                    <p>{{ $warning }}</p>
                                @endforeach
                            </div>
                        @endif

                        @if($b->openSpecialCases->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach($b->openSpecialCases as $case)
                                    <span class="status-badge {{ $case->severity === 'warning' ? 'status-pending' : 'status-completed' }}">{{ $case->message }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('bookings.show', ['booking' => $b, 'return' => $returnUrl]) }}" class="btn-gold" @click="storeScroll(); loading = true">{{ __("View") }}</a>
                            @if($b->reservation_fee_amount || $b->estimated_price !== null)
                                <a href="{{ route('bookings.receipt.show', $b) }}" class="btn-ghost" @click="storeScroll(); loading = true">{{ __("Receipt") }}</a>
                            @endif
                            @if($b->isPending())
                                <form method="POST" action="{{ route('manage.bookings.approve', $b) }}">@csrf
                                    <button class="btn-ghost">{{ __("Book") }}</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="px-4 py-12 text-center text-brand-navy/55">{{ __("No bookings match your filters.") }}</div>
                @endforelse
            </div>

            <div class="hidden lg:block overflow-x-auto pb-28">
                <table class="w-full text-xs" style="min-width: 1180px; table-layout: fixed;">
                    <colgroup>
                        <col style="width: 16%;">
                        <col style="width: 20%;">
                        <col style="width: 13%;">
                        <col style="width: 15%;">
                        <col style="width: 16%;">
                        <col style="width: 13%;">
                        <col style="width: 7%;">
                    </colgroup>
                    <thead class="bg-brand-navy/5">
                        <tr class="text-left text-xs uppercase tracking-[0.12em] text-brand-navy/65">
                            <th class="px-4 py-3">{{ __("Who") }}</th>
                            <th class="px-4 py-3">{{ __("What / Why") }}</th>
                            <th class="px-4 py-3">{{ __("When") }}</th>
                            <th class="px-4 py-3">{{ __("Where / Which") }}</th>
                            <th class="px-4 py-3">{{ __("Payment") }}</th>
                            <th class="px-4 py-3">{{ __("Status") }}</th>
                            <th class="px-3 py-3 text-right">{{ __("Actions") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $b)
                            <tr class="border-t border-brand-navy/10 hover:bg-brand-navy/[0.025]">
                                <td class="px-4 py-4 align-top">
                                    <div class="text-[0.68rem] text-brand-navy/45 mb-1">#{{ $b->id }}</div>
                                    <div class="font-medium text-brand-navy leading-snug wrap-anywhere">{{ $b->customer_name }}</div>
                                    <div class="mt-1 text-[0.68rem] text-brand-navy/55 leading-snug wrap-anywhere">{{ $b->contact }}</div>
                                    @if($b->organization)
                                        <div class="mt-1 text-[0.68rem] text-brand-navy/45 leading-snug wrap-anywhere">{{ $b->organization }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="font-medium text-brand-navy leading-snug wrap-anywhere">{{ $b->servicePackage?->name ?? __('No package') }}</div>
                                    <div class="mt-1 text-[0.68rem] text-brand-navy/55">
                                        {{ $b->format }}@if($b->participant_count) · {{ $b->participant_count }} {{ __("participants") }}@endif
                                    </div>
                                    <div class="mt-2 text-[0.7rem] text-brand-navy/75 leading-snug wrap-anywhere">{{ $b->purpose }}</div>
                                    @if($b->customer_notes)
                                        <div class="mt-2 max-h-10 overflow-hidden text-[0.68rem] leading-snug text-brand-navy/50 whitespace-pre-line wrap-anywhere">{{ $b->customer_notes }}</div>
                                    @else
                                        <div class="mt-2 text-[0.68rem] text-brand-navy/35">{{ __("No notes") }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="font-medium text-brand-navy">{{ optional($b->starts_at)->format('M d, Y') }}</div>
                                    <div class="mt-1 text-[0.68rem] text-brand-navy/55">{{ optional($b->starts_at)->format('H:i') }}-{{ optional($b->ends_at)->format('H:i') }}</div>
                                    <div class="mt-2 text-[0.68rem] text-brand-navy/45">{{ __("Requested") }} {{ optional($b->created_at)->format('M d, H:i') }}</div>
                                    @if($b->approved_at)
                                        <div class="mt-1 text-[0.68rem] text-brand-navy/45">{{ __("Booked") }} {{ $b->approved_at->format('M d, H:i') }}</div>
                                    @endif
                                    @if($b->cancelled_at)
                                        <div class="mt-1 text-[0.68rem] text-rose-700">{{ __("Cancelled") }} {{ $b->cancelled_at->format('M d, H:i') }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="font-medium text-brand-navy leading-snug wrap-anywhere">{{ $b->classroom?->name ?? __('To be confirmed') }}</div>
                                    <div class="mt-1 text-[0.68rem] text-brand-navy/55 leading-snug wrap-anywhere">
                                        {{ collect([$b->classroom?->location, $b->classroom?->floor, $b->classroom?->room_number ? __('Room').' '.$b->classroom->room_number : null])->filter()->implode(' · ') ?: __('No room detail') }}
                                    </div>
                                    @if($b->classroom?->address)
                                        <div class="mt-1 text-[0.68rem] text-brand-navy/45 leading-snug wrap-anywhere">{{ $b->classroom->address }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 align-top">
                                    @php
                                        $discountAmount = (int) ($b->special_discount_amount ?? 0);
                                        $baseEstimate = $b->estimated_price !== null ? (int) $b->estimated_price + $discountAmount : null;
                                    @endphp
                                    @if($b->estimated_price !== null)
                                        <div class="font-medium text-brand-navy">{{ __("Total") }}: {{ \App\Support\Money::format($b->estimated_price) }}</div>
                                    @else
                                        <div class="font-medium text-brand-navy/45">{{ __("No estimate") }}</div>
                                    @endif
                                    @if($discountAmount)
                                        <div class="mt-1 text-[0.68rem] text-brand-navy/45">
                                            {{ __("Base") }} {{ \App\Support\Money::format($baseEstimate) }} · {{ __("Discount") }} -{{ \App\Support\Money::format($discountAmount) }}
                                        </div>
                                    @endif
                                    <div class="mt-1 text-[0.68rem] text-brand-navy/55">
                                        {{ __("Reservation") }}:
                                        @if($b->reservation_fee_amount)
                                            {{ \App\Support\Money::format($b->reservation_fee_amount) }} · {{ rtrim(rtrim((string) $b->reservation_fee_percent, '0'), '.') }}%
                                        @else
                                            {{ __("Not required") }}
                                        @endif
                                    </div>
                                    <div class="mt-1 text-[0.68rem] text-brand-navy/55">{{ __("Payment") }}: {{ $b->paymentMethodLabel() }}</div>
                                    <div class="mt-1 text-[0.68rem] text-brand-navy/55">{{ __("Type") }}: {{ $b->paymentScopeLabel() }}</div>
                                    @if($b->reservation_fee_amount)
                                        <div class="mt-1 text-[0.68rem] text-brand-navy/45">{{ __("Ref") }}: {{ $b->paymentReference() }}</div>
                                    @endif
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        <span class="status-badge {{ $b->reservation_fee_paid_at ? 'status-approved' : ($b->reservation_fee_amount ? 'status-pending' : 'status-cancelled') }}">{{ __("Reservation") }}: {{ $b->reservationFeeStatusLabel() }}</span>
                                        @if($b->isFullPaymentSelected() || $b->full_payment_paid_at)
                                            <span class="status-badge {{ $b->full_payment_paid_at ? 'status-approved' : 'status-pending' }}">{{ __("Full") }}: {{ $b->fullPaymentStatusLabel() }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="text-[0.62rem] uppercase tracking-[0.16em] text-brand-navy/40 mb-1">{{ __("Workflow") }}</div>
                                    <span class="status-badge {{ $b->workflowBadgeClass() }}">{{ $b->workflowStageLabel() }}</span>
                                    <div class="mt-1 text-[0.68rem] text-brand-navy/45">{{ __("System") }}: {{ $b->statusLabel() }}</div>
                                    @if($b->workflowWarnings())
                                        <div class="mt-2 space-y-1">
                                            @foreach($b->workflowWarnings() as $warning)
                                                <div><span class="status-badge status-pending">{{ $warning }}</span></div>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if($b->openSpecialCases->isNotEmpty())
                                        <div class="mt-2 space-y-1">
                                            @foreach($b->openSpecialCases as $case)
                                                <div><span class="status-badge {{ $case->severity === 'warning' ? 'status-pending' : 'status-completed' }}">{{ $case->message }}</span></div>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if($b->cancellation_reason)
                                        <p class="mt-2 text-[0.68rem] text-brand-navy/50 leading-snug wrap-anywhere">{{ $b->cancellation_reason }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-4 align-top text-right">
		                                    <div
		                                        class="relative inline-block"
		                                        x-data="bookingActionMenu()"
		                                        @keydown.escape.window="close()"
		                                        @resize.window="reposition()"
		                                        @scroll.window="reposition()"
		                                        @click.outside="close()"
		                                    >
		                                        <button
		                                            type="button"
		                                            class="action-menu-button"
		                                            aria-label="{{ __('Booking actions') }}"
		                                            aria-haspopup="menu"
		                                            x-ref="trigger"
		                                            :aria-expanded="open.toString()"
		                                            @click="toggle()"
		                                        >
		                                            <svg width="18" height="18" style="width:18px;height:18px;flex:0 0 18px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
		                                                <circle cx="12" cy="5" r="1.8" />
		                                                <circle cx="12" cy="12" r="1.8" />
		                                                <circle cx="12" cy="19" r="1.8" />
		                                            </svg>
		                                        </button>
		                                        <div
		                                            x-ref="panel"
		                                            class="action-menu-panel"
		                                            x-show="open"
		                                            x-cloak
		                                            x-transition
		                                            :style="panelStyle"
		                                            role="menu"
		                                        >
		                                        <a
		                                            href="{{ route('bookings.show', ['booking' => $b, 'return' => $returnUrl]) }}"
		                                            class="action-menu-item"
		                                            data-tooltip="{{ __('Open booking details and history.') }}"
		                                            @mouseenter="$dispatch('action-tooltip-show', { text: $el.dataset.tooltip, x: $event.clientX, y: $event.clientY })"
		                                            @mousemove="$dispatch('action-tooltip-move', { x: $event.clientX, y: $event.clientY })"
		                                            @mouseleave="$dispatch('action-tooltip-hide')"
		                                            data-restore-scroll
		                                            @click="storeScroll(); loading = true"
		                                            role="menuitem"
		                                        >{{ __("View") }}</a>
		                                        @if($b->reservation_fee_amount || $b->estimated_price !== null)
		                                            <a
		                                                href="{{ route('bookings.receipt.show', $b) }}"
		                                                class="action-menu-item"
		                                                data-tooltip="{{ __('Open the printable receipt preview.') }}"
		                                                @mouseenter="$dispatch('action-tooltip-show', { text: $el.dataset.tooltip, x: $event.clientX, y: $event.clientY })"
		                                                @mousemove="$dispatch('action-tooltip-move', { x: $event.clientX, y: $event.clientY })"
		                                                @mouseleave="$dispatch('action-tooltip-hide')"
		                                                @click="storeScroll(); loading = true"
		                                                role="menuitem"
		                                            >{{ __("Receipt preview") }}</a>
		                                        @endif
		                                        @can('reschedule', $b)
		                                            <a
		                                                href="{{ route('bookings.reschedule.edit', $b) }}"
		                                                class="action-menu-item"
		                                                data-tooltip="{{ __('Change the scheduled date or time.') }}"
		                                                @mouseenter="$dispatch('action-tooltip-show', { text: $el.dataset.tooltip, x: $event.clientX, y: $event.clientY })"
		                                                @mousemove="$dispatch('action-tooltip-move', { x: $event.clientX, y: $event.clientY })"
		                                                @mouseleave="$dispatch('action-tooltip-hide')"
		                                                @click="storeScroll(); loading = true"
		                                                role="menuitem"
		                                            >{{ __("Reschedule") }}</a>
		                                        @endcan
		                                        @if($b->isPending())
		                                            <form method="POST" action="{{ route('manage.bookings.approve', $b) }}">@csrf
		                                                <button class="action-menu-item action-menu-item-success" data-tooltip="{{ __('Approve this pending booking.') }}" @mouseenter="$dispatch('action-tooltip-show', { text: $el.dataset.tooltip, x: $event.clientX, y: $event.clientY })" @mousemove="$dispatch('action-tooltip-move', { x: $event.clientX, y: $event.clientY })" @mouseleave="$dispatch('action-tooltip-hide')" role="menuitem">{{ __("Book") }}</button>
		                                            </form>
		                                            <form method="POST" action="{{ route('manage.bookings.reject', $b) }}" onsubmit="this.reason.value = prompt('Reason (optional)') || '';">@csrf
		                                                <input type="hidden" name="reason">
		                                                <button class="action-menu-item action-menu-item-danger" data-tooltip="{{ __('Reject this booking request.') }}" @mouseenter="$dispatch('action-tooltip-show', { text: $el.dataset.tooltip, x: $event.clientX, y: $event.clientY })" @mousemove="$dispatch('action-tooltip-move', { x: $event.clientX, y: $event.clientY })" @mouseleave="$dispatch('action-tooltip-hide')" role="menuitem">{{ __("Reject") }}</button>
		                                            </form>
		                                        @endif
			                                        @if($b->reservation_fee_amount && !$b->isCancelled() && !$b->isRejected())
			                                            @if($b->reservation_fee_paid_at)
			                                                <form method="POST" action="{{ route('manage.bookings.reservation-fee.unpaid', $b) }}">@csrf
			                                                    <button class="action-menu-item" data-tooltip="{{ __('Mark only the reservation fee as unpaid.') }}" @mouseenter="$dispatch('action-tooltip-show', { text: $el.dataset.tooltip, x: $event.clientX, y: $event.clientY })" @mousemove="$dispatch('action-tooltip-move', { x: $event.clientX, y: $event.clientY })" @mouseleave="$dispatch('action-tooltip-hide')" role="menuitem">{{ __("Mark reservation unpaid") }}</button>
			                                                </form>
			                                            @else
			                                                <form method="POST" action="{{ route('manage.bookings.reservation-fee.paid', $b) }}">@csrf
			                                                    <button class="action-menu-item action-menu-item-success" data-tooltip="{{ __('Record only the reservation fee as paid.') }}" @mouseenter="$dispatch('action-tooltip-show', { text: $el.dataset.tooltip, x: $event.clientX, y: $event.clientY })" @mousemove="$dispatch('action-tooltip-move', { x: $event.clientX, y: $event.clientY })" @mouseleave="$dispatch('action-tooltip-hide')" role="menuitem">{{ __("Mark reservation paid") }}</button>
			                                                </form>
			                                            @endif
			                                        @endif
			                                        @if($b->estimated_price !== null && !$b->isCancelled() && !$b->isRejected())
			                                            @if($b->full_payment_paid_at)
			                                                <form method="POST" action="{{ route('manage.bookings.full-payment.unpaid', $b) }}">@csrf
			                                                    <button class="action-menu-item" data-tooltip="{{ __('Mark the full booking payment as unpaid while keeping any reservation payment record.') }}" @mouseenter="$dispatch('action-tooltip-show', { text: $el.dataset.tooltip, x: $event.clientX, y: $event.clientY })" @mousemove="$dispatch('action-tooltip-move', { x: $event.clientX, y: $event.clientY })" @mouseleave="$dispatch('action-tooltip-hide')" role="menuitem">{{ __("Mark full unpaid") }}</button>
			                                                </form>
			                                            @else
			                                                <form method="POST" action="{{ route('manage.bookings.full-payment.paid', $b) }}">@csrf
			                                                    <button class="action-menu-item action-menu-item-success" data-tooltip="{{ __('Record the full booking amount as paid at the counter.') }}" @mouseenter="$dispatch('action-tooltip-show', { text: $el.dataset.tooltip, x: $event.clientX, y: $event.clientY })" @mousemove="$dispatch('action-tooltip-move', { x: $event.clientX, y: $event.clientY })" @mouseleave="$dispatch('action-tooltip-hide')" role="menuitem">{{ __("Mark full paid") }}</button>
			                                                </form>
			                                            @endif
			                                        @endif
			                                        @if(!$b->isCancelled() && !$b->isRejected())
			                                            <form method="POST" action="{{ route('manage.bookings.cancel', $b) }}" onsubmit="this.reason.value = prompt('Cancellation reason (optional)') || ''; return confirm('Cancel this booking?');">@csrf
		                                                <input type="hidden" name="reason">
		                                                <button class="action-menu-item action-menu-item-danger" data-tooltip="{{ __('Cancel this booking and record a reason.') }}" @mouseenter="$dispatch('action-tooltip-show', { text: $el.dataset.tooltip, x: $event.clientX, y: $event.clientY })" @mousemove="$dispatch('action-tooltip-move', { x: $event.clientX, y: $event.clientY })" @mouseleave="$dispatch('action-tooltip-hide')" role="menuitem">{{ __("Cancel") }}</button>
		                                            </form>
		                                        @endif
		                                        </div>
		                                    </div>
		                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-12 text-center text-brand-navy/55">{{ __("No bookings match your filters.") }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <p class="text-sm text-brand-navy/55">
                {{ __('Showing') }} {{ $pagination['from'] }}-{{ $pagination['to'] }} {{ __('of') }} {{ $pagination['total'] }}
            </p>

            <nav class="flex flex-wrap items-center gap-2 text-sm" aria-label="Booking management pages">
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
                    aria-label="First booking page"
                >&lt;&lt;</a>
                <a
                    href="{{ $pagination['previous_url'] ?? '#' }}"
                    class="btn-ghost {{ $pagination['previous_url'] ? '' : 'pointer-events-none opacity-40' }}"
                    @click="storeScroll(); loading = true"
                    aria-label="Previous booking page"
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
                    aria-label="Next booking page"
                >&gt;</a>
                <a
                    href="{{ $pagination['last_url'] }}"
                    class="btn-ghost {{ $pagination['current_page'] === $pagination['last_page'] ? 'pointer-events-none opacity-40' : '' }}"
                    @click="storeScroll(); loading = true"
                    aria-label="Last booking page"
                >&gt;&gt;</a>
            </nav>
        </div>
    </div>

    <script>
	        function bookingManagementList() {
		            return {
		                loading: false,
	                settingsOpen: false,
	                tooltip: {
	                    visible: false,
	                    text: '',
	                    style: 'left: 0; top: 0;',
	                },
	                storageKey: 'ican:booking-management-scroll:' + window.location.pathname + window.location.search,
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
	                showTooltip(text, x, y) {
	                    this.tooltip.text = text;
	                    this.tooltip.visible = true;
	                    this.$nextTick(() => this.moveTooltip(x, y));
	                },
	                moveTooltip(x, y) {
	                    if (! this.tooltip.visible) {
	                        return;
	                    }

	                    const el = this.$refs.actionTooltip;
	                    const width = el?.offsetWidth || 224;
	                    const height = el?.offsetHeight || 48;
	                    const gap = 14;
	                    const margin = 10;

	                    let left = x + gap;
	                    let top = y + gap;

	                    if (left + width > window.innerWidth - margin) {
	                        left = x - width - gap;
	                    }

	                    if (top + height > window.innerHeight - margin) {
	                        top = y - height - gap;
	                    }

	                    this.tooltip.style = `left: ${Math.round(Math.max(margin, left))}px; top: ${Math.round(Math.max(margin, top))}px;`;
	                },
	                hideTooltip() {
	                    this.tooltip.visible = false;
	                },
		            };
		        }

	        function bookingActionMenu() {
	            return {
	                open: false,
	                panelStyle: '',
	                toggle() {
	                    this.open ? this.close() : this.show();
	                },
	                show() {
	                    this.open = true;
	                    this.$nextTick(() => this.reposition());
	                },
	                close() {
	                    this.open = false;
	                },
	                reposition() {
	                    if (! this.open || ! this.$refs.trigger) {
	                        return;
	                    }

	                    const rect = this.$refs.trigger.getBoundingClientRect();
	                    const panel = this.$refs.panel;
	                    const margin = 12;
	                    const gap = 6;
	                    const width = panel?.offsetWidth || 192;
	                    const height = panel?.offsetHeight || 240;

	                    let left = rect.right - width;
	                    left = Math.max(margin, Math.min(left, window.innerWidth - width - margin));

	                    let top = rect.bottom + gap;
	                    if (top + height > window.innerHeight - margin) {
	                        top = Math.max(margin, rect.top - height - gap);
	                    }

	                    this.panelStyle = `left: ${Math.round(left)}px; top: ${Math.round(top)}px;`;
	                },
	            };
	        }
	    </script>
</x-app-layout>
