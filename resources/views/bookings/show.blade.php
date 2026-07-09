<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-end gap-4 flex-wrap">
            <div>
                <p class="eyebrow mb-1">Booking #{{ $booking->id }}</p>
                <h1 class="font-serif text-4xl text-brand-navy">{{ $booking->purpose }}</h1>
            </div>
            <span class="status-badge status-{{ $booking->status }}">{{ $booking->statusLabel() }}</span>
        </div>
    </x-slot>

    @php
        $room = $booking->classroom;
        $roomLocation = collect([
            $room?->location,
            $room?->floor,
            $room?->room_number ? __('Room').' '.$room->room_number : null,
        ])->filter()->implode(' · ');
        $arrivalInstructions = $room?->arrival_instructions ?: ($bookingSettings['arrival_instructions'] ?? '');
        $paymentInstructions = $bookingSettings['payment_instructions'] ?? '';
        $canManageBooking = auth()->user()?->hasAnyRole(['super_admin', 'admin', 'staff']) ?? false;
    @endphp

    <div class="max-w-6xl mx-auto px-6 py-12 space-y-8">

        @if (session('booking_saved'))
            <div class="border border-emerald-400 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
                {{ session('booking_saved') }}
            </div>
        @endif

        <div class="card p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="eyebrow mb-2">{{ __("Booking workflow") }}</p>
                    <h2 class="font-serif text-3xl text-brand-navy">{{ $booking->workflowStageLabel() }}</h2>
                </div>
                <span class="status-badge {{ $booking->workflowBadgeClass() }}">{{ $booking->workflowStage() }}</span>
            </div>

            @if($booking->workflowWarnings())
                <div class="mt-4 border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    @foreach($booking->workflowWarnings() as $warning)
                        <p>{{ $warning }}</p>
                    @endforeach
                </div>
            @endif

            <div class="mt-6 grid grid-cols-1 md:grid-cols-5 gap-3">
                @foreach($booking->workflowTimeline() as $event)
                    <div class="border border-brand-navy/10 px-4 py-3">
                        <span class="status-badge {{ $event['state'] === 'complete' ? 'status-approved' : ($event['state'] === 'current' ? 'status-pending' : 'status-cancelled') }}">{{ $event['state'] }}</span>
                        <p class="mt-3 font-medium text-brand-navy">{{ __($event['label']) }}</p>
                        <p class="mt-1 text-xs text-brand-navy/55">{{ __($event['description']) }}</p>
                        @if($event['at'])
                            <p class="mt-2 text-xs uppercase tracking-[0.16em] text-brand-navy/40">{{ $event['at']->format('M d, Y H:i') }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        @if($room)
            <div class="card overflow-hidden">
                <x-room-gallery-slider :room="$room" height="h-64 md:h-80" :frame="false" />
                <div class="p-6">
                    <p class="eyebrow mb-2">{{ __("Which room") }}</p>
                    <h2 class="font-serif text-3xl text-brand-navy mb-2">{{ $room->name }}</h2>
                    <p class="text-brand-navy/65 text-sm">{{ $roomLocation ?: $room->location }}{{ $room->address ? ' · '.$room->address : '' }}</p>
                </div>
            </div>
        @endif

        <div class="grid lg:grid-cols-4 md:grid-cols-2 gap-5">
            <div class="card p-6 min-w-0">
                <p class="eyebrow mb-3">{{ __("Who") }}</p>
                <p class="font-serif text-2xl text-brand-navy mb-1 wrap-anywhere">{{ $booking->customer_name }}</p>
                <p class="text-brand-navy/65 text-sm wrap-anywhere">{{ $booking->contact }}</p>
                @if($booking->organization)
                    <p class="text-brand-navy/45 text-sm mt-1 wrap-anywhere">{{ $booking->organization }}</p>
                @endif
                @if($booking->user)
                    <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/40 mt-3 wrap-anywhere">{{ __("Account") }}: {{ $booking->user->email }}</p>
                @endif
            </div>

            <div class="card p-6">
                <p class="eyebrow mb-3">{{ __("What") }}</p>
                <p class="font-serif text-2xl text-brand-navy mb-1">{{ $booking->purpose }}</p>
                <ul class="text-brand-navy/70 text-sm space-y-1">
                    <li>{{ __("Format") }}: {{ $booking->format }}</li>
                    @if($booking->participant_count)<li>{{ __("Participants") }}: {{ $booking->participant_count }}</li>@endif
                    @if($booking->servicePackage)<li>{{ __("Package") }}: {{ $booking->servicePackage->name }}</li>@endif
                </ul>
            </div>

            <div class="card p-6">
                <p class="eyebrow mb-3">{{ __("When") }}</p>
                <p class="font-serif text-2xl text-brand-navy mb-1">{{ optional($booking->starts_at)->format('M d, Y') }}</p>
                <p class="text-brand-navy/65">{{ optional($booking->starts_at)->format('H:i') }} - {{ optional($booking->ends_at)->format('H:i') }}</p>
                <div class="mt-3 space-y-1 text-xs text-brand-navy/50">
                    <p>{{ __("Requested") }}: {{ optional($booking->created_at)->format('M d, Y H:i') }}</p>
                    @if($booking->approved_at)<p>{{ __("Booked") }}: {{ $booking->approved_at->format('M d, Y H:i') }}</p>@endif
                    @if($booking->rejected_at)<p>{{ __("Rejected") }}: {{ $booking->rejected_at->format('M d, Y H:i') }}</p>@endif
                    @if($booking->reservation_fee_paid_at)<p>{{ __("Reservation fee paid") }}: {{ $booking->reservation_fee_paid_at->format('M d, Y H:i') }}</p>@endif
                    @if($booking->full_payment_paid_at)<p>{{ __("Full payment paid") }}: {{ $booking->full_payment_paid_at->format('M d, Y H:i') }}</p>@endif
                    @if($booking->cancelled_at)<p>{{ __("Cancelled") }}: {{ $booking->cancelled_at->format('M d, Y H:i') }}</p>@endif
                </div>
            </div>

            <div class="card p-6">
                <p class="eyebrow mb-3">{{ __("Where") }}</p>
                <p class="font-serif text-2xl text-brand-navy mb-1">{{ $room?->name ?? __("To be confirmed") }}</p>
                @if($roomLocation)
                    <p class="text-brand-navy/65 text-sm">{{ $roomLocation }}</p>
                @endif
                @if($room?->address)
                    <p class="text-brand-navy/45 text-sm mt-1">{{ $room->address }}</p>
                @endif
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-5">
            <div class="card p-6">
                <p class="eyebrow mb-4">{{ __("Reservation fee") }}</p>
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-brand-navy/45 uppercase text-xs tracking-[0.18em]">{{ __("Calculated cost") }}</p>
                        <p class="font-serif text-3xl text-brand-navy mt-1">{{ $pricing['total'] !== null ? \App\Support\Money::format($pricing['total']) : '—' }}</p>
                    </div>
                    <span class="status-badge {{ $booking->reservation_fee_paid_at ? 'status-approved' : ($booking->reservation_fee_amount ? 'status-pending' : 'status-cancelled') }}">{{ $booking->reservationFeeStatusLabel() }}</span>
                </div>
                <div class="mt-3 grid gap-2 text-sm text-brand-navy/70">
                    @if($pricing['room_rate'] !== null)
                        <p>{{ __("Room") }}: {{ \App\Support\Money::format($pricing['room_rate']) }}/{{ __("hr") }} × {{ $pricing['hours_label'] }} = {{ \App\Support\Money::format($pricing['room']) }}</p>
                    @endif
	                    @if($booking->servicePackage)
	                        <p>{{ __("Package") }}: {{ $booking->servicePackage->name }} · {{ \App\Support\Money::format($pricing['package']) }}</p>
	                    @endif
	                    @if($pricing['discount_amount'])
	                        <p>{{ __("Discount") }}: -{{ \App\Support\Money::format($pricing['discount_amount']) }} · {{ rtrim(rtrim((string) $pricing['discount_percent'], '0'), '.') }}%</p>
	                    @endif
	                    @if($booking->reservation_fee_amount)
	                        <p>{{ __("Reservation") }}: {{ \App\Support\Money::format($booking->reservation_fee_amount) }} · {{ rtrim(rtrim((string) $booking->reservation_fee_percent, '0'), '.') }}%</p>
	                    @endif
                    <p>{{ __("Payment method") }}: {{ $booking->paymentMethodLabel() }}</p>
                    <p>{{ __("Payment type") }}: {{ $booking->paymentScopeLabel() }}</p>
                    @if($booking->estimated_price !== null)
                        <p>{{ __("Full payment") }}: {{ $booking->fullPaymentStatusLabel() }}</p>
                    @endif
                    @if($booking->reservationFeeMarkedPaidBy)
                        <p>{{ __("Marked paid by") }}: {{ $booking->reservationFeeMarkedPaidBy->name }}</p>
                    @endif
                    @if($booking->fullPaymentMarkedPaidBy)
                        <p>{{ __("Full payment marked by") }}: {{ $booking->fullPaymentMarkedPaidBy->name }}</p>
                    @endif
                </div>
                @if($paymentInstructions && !$booking->selectedPaymentPaidAt())
                    <p class="mt-4 text-sm text-brand-navy/60 whitespace-pre-line">{{ $paymentInstructions }}</p>
                @endif
                @if($booking->reservation_fee_amount || $booking->estimated_price !== null)
                    <a href="{{ route('bookings.payment.edit', $booking) }}" class="btn-gold inline-flex mt-5">
                        {{ $booking->selectedPaymentPaidAt() ? __("View paid receipt") : __("Counter payment slip") }}
                    </a>
                @endif
            </div>

            <div class="card p-6">
                <p class="eyebrow mb-4">{{ __("Setup") }}</p>
                <ul class="text-brand-navy/75 text-sm space-y-2">
                    @if($booking->equipmentRequestLabels())
                        <li><span class="text-brand-navy/45 uppercase text-xs tracking-[0.18em] mr-2">{{ __("Equipment") }}</span> {{ implode(', ', $booking->equipmentRequestLabels()) }}</li>
                    @endif
                    @if($booking->snackBeverageRequestLabels())
                        <li><span class="text-brand-navy/45 uppercase text-xs tracking-[0.18em] mr-2">{{ __("Coffee/snacks") }}</span> {{ implode(', ', $booking->snackBeverageRequestLabels()) }}</li>
                    @endif
                    @if(! $booking->equipmentRequestLabels() && ! $booking->snackBeverageRequestLabels())
                        <li class="text-brand-navy/50">{{ __("No additional setup requested.") }}</li>
                    @endif
                </ul>
	                @if($booking->equipment_notes || $booking->snack_beverage_notes)
	                    <div class="mt-4 space-y-3 text-sm text-brand-navy/70">
	                        @if($booking->equipment_notes)<p class="whitespace-pre-line">{{ __("Equipment notes") }}: {{ $booking->equipment_notes }}</p>@endif
	                        @if($booking->snack_beverage_notes)<p class="whitespace-pre-line">{{ __("Coffee/snack notes") }}: {{ $booking->snack_beverage_notes }}</p>@endif
	                    </div>
	                @endif
	                @if($booking->customer_notes)
	                    <div class="mt-5 border-t border-brand-navy/10 pt-4">
	                        <p class="eyebrow mb-2">{{ __("Customer notes") }}</p>
	                        <p class="text-brand-navy/75 text-sm whitespace-pre-line">{{ $booking->customer_notes }}</p>
	                    </div>
	                @endif
	            </div>
	        </div>
	
	        @if(($canManageBooking && $booking->internal_notes) || $booking->cancellation_reason)
	            <div class="grid md:grid-cols-2 gap-5">
	                @if($canManageBooking && $booking->internal_notes)
	                    <div class="card p-6">
	                        <p class="eyebrow mb-2">{{ __("Internal notes") }}</p>
                        <p class="text-brand-navy/75 whitespace-pre-line">{{ $booking->internal_notes }}</p>
                    </div>
                @endif
                @if($booking->cancellation_reason)
                    <div class="card p-6">
                        <p class="eyebrow mb-2">{{ __("Cancellation reason") }}</p>
                        <p class="text-brand-navy/75 whitespace-pre-line">{{ $booking->cancellation_reason }}</p>
                    </div>
                @endif
            </div>
        @endif

        @if($arrivalInstructions || $room?->map_embed_url)
            <div class="card overflow-hidden">
                <div class="p-6">
                    <p class="eyebrow mb-2">{{ __("How to get there") }}</p>
                    @if($arrivalInstructions)
                        <p class="text-brand-navy/70 whitespace-pre-line">{{ $arrivalInstructions }}</p>
                    @endif
                    @if($room?->address)
                        <a href="https://www.google.com/maps?q={{ urlencode($room->address) }}" target="_blank" rel="noopener" class="inline-flex mt-4 text-xs uppercase tracking-[0.18em] text-brand-navy/65 hover:text-brand-navy">{{ __("Open in maps") }}</a>
                    @endif
                </div>
                @if($room?->map_embed_url)
                    <iframe src="{{ $room->map_embed_url }}" width="100%" height="320" style="border:0;filter:invert(0.92) hue-rotate(180deg) saturate(0.8);" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                @endif
            </div>
        @endif

        @if($booking->specialCases->isNotEmpty() || $activity->isNotEmpty() || ($canManageBooking && $booking->notifications->isNotEmpty()))
            <div class="card p-6">
                <p class="eyebrow mb-4">{{ __("History") }}</p>
                @if($booking->specialCases->isNotEmpty())
                    <div class="mb-5 space-y-2">
                        @foreach($booking->specialCases as $case)
                            <div class="border border-brand-navy/10 p-3">
                                <div class="flex flex-wrap justify-between gap-2">
                                    <span class="status-badge {{ $case->severity === 'warning' ? 'status-pending' : 'status-completed' }}">{{ $case->message }}</span>
                                    <span class="text-xs text-brand-navy/40">{{ $case->created_at->format('M d, Y H:i') }}</span>
                                </div>
                                @if($case->details)
                                    <p class="mt-2 text-sm text-brand-navy/60">{{ $case->details }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
                @if($activity->isNotEmpty())
                    <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/45 mb-3">{{ __("Audit log") }}</p>
                    <ul class="space-y-3">
                        @foreach($activity as $event)
                            <li class="flex gap-4 text-sm">
                                <span class="text-brand-navy/40 w-32 shrink-0">{{ $event->created_at->format('M d, H:i') }}</span>
                                <span class="text-brand-navy/75">{{ ucfirst($event->description) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @if($canManageBooking && $booking->notifications->isNotEmpty())
                    <div class="mt-6 border-t border-brand-navy/10 pt-5">
                        <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/45 mb-3">{{ __("Notification log") }}</p>
                        <ul class="space-y-3">
                            @foreach($booking->notifications->sortByDesc('created_at')->take(8) as $notification)
                                <li class="flex flex-wrap justify-between gap-3 text-sm">
                                    <span class="text-brand-navy/75">
                                        {{ class_basename($notification->notification_type) }} · {{ $notification->audience }} · {{ $notification->recipient ?? __('No recipient') }}
                                    </span>
                                    <span class="status-badge {{ $notification->status === \App\Models\BookingNotification::STATUS_SENT ? 'status-approved' : ($notification->status === \App\Models\BookingNotification::STATUS_FAILED ? 'status-rejected' : 'status-pending') }}">{{ $notification->status }}</span>
                                    @if($notification->error)
                                        <span class="basis-full text-xs text-red-700">{{ $notification->error }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        <div class="flex flex-wrap gap-3 justify-between">
            <a href="{{ $returnUrl }}" class="btn-ghost">← {{ __("Back") }}</a>
            <div class="flex flex-wrap gap-3">
                @if($booking->reservation_fee_amount || $booking->estimated_price !== null)
                    <a href="{{ route('bookings.payment.edit', $booking) }}" class="btn-gold">
                        {{ $booking->selectedPaymentPaidAt() ? __("View paid receipt") : __("Counter payment slip") }}
                    </a>
                @endif
                @can('reschedule', $booking)
                    <a href="{{ route('bookings.reschedule.edit', $booking) }}" class="btn-gold">{{ __("Reschedule") }}</a>
                @endcan
                @can('cancel', $booking)
                    <form method="POST" action="{{ route('bookings.cancel', $booking) }}" onsubmit="this.reason.value = prompt('Cancellation reason (optional)') || ''; return confirm('Cancel this booking?');">
                        @csrf
                        <input type="hidden" name="reason">
                        <button type="submit" class="btn-ghost">{{ __("Cancel booking") }}</button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
