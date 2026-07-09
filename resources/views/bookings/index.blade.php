<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-end gap-4 flex-wrap">
            <div>
                <p class="eyebrow mb-1">{{ __('Bookings') }}</p>
                <h1 class="font-serif text-4xl text-brand-navy">{{ __('My booking requests') }}</h1>
            </div>
            <a href="{{ route('bookings.create') }}" class="btn-gold">{{ __('New booking') }}</a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto px-6 py-12">
        @if (session('booking_saved'))
            <div class="mb-6 border border-emerald-400 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
                {{ session('booking_saved') }}
            </div>
        @endif

        <div class="space-y-4">
            @forelse ($bookings as $booking)
                <a href="{{ route('bookings.show', $booking) }}" class="card p-6 block hover:-translate-y-0.5 transition-transform">
                    <div class="flex justify-between gap-4 flex-wrap">
                        <div class="flex gap-5 items-start flex-1">
                            @if($booking->classroom)
                                <div class="w-20 h-20 bg-cover bg-center shrink-0" style="background-image:url('{{ $booking->classroom->hero_image }}')"></div>
                            @endif
                            <div>
                                <p class="eyebrow mb-1">#{{ $booking->id }} · {{ optional($booking->starts_at)->format('M d, Y') }}</p>
                                <h3 class="font-serif text-2xl text-brand-navy mb-1">{{ $booking->purpose }}</h3>
                                <p class="text-brand-navy/65 text-sm">
                                    {{ $booking->classroom?->name ?? '—' }}
                                    · {{ optional($booking->starts_at)->format('H:i') }}–{{ optional($booking->ends_at)->format('H:i') }}
                                    · {{ $booking->format }}
                                </p>
                                @if($booking->estimated_price)
                                    <p class="text-brand-navy/45 text-xs mt-2 uppercase tracking-[0.18em]">{{ __('Estimated') }} · {{ \App\Support\Money::format($booking->estimated_price) }}</p>
                                @endif
	                                @if($booking->reservation_fee_amount)
	                                    <p class="text-brand-navy/45 text-xs mt-1 uppercase tracking-[0.18em]">{{ __('Reservation') }} · {{ \App\Support\Money::format($booking->reservation_fee_amount) }} · {{ $booking->reservationFeeStatusLabel() }}</p>
	                                @endif
	                                <p class="text-brand-navy/45 text-xs mt-1 uppercase tracking-[0.18em]">{{ __('Payment') }} · {{ $booking->paymentScopeLabel() }} · {{ $booking->paymentMethodLabel() }}</p>
	                                <p class="text-brand-navy/45 text-xs mt-1 uppercase tracking-[0.18em]">{{ __('Workflow') }} · {{ $booking->workflowStageLabel() }}</p>
	                            </div>
                        </div>
                        <span class="status-badge {{ $booking->workflowBadgeClass() }} self-start">{{ $booking->workflowStageLabel() }}</span>
                    </div>
                </a>
            @empty
                <div class="card p-12 text-center">
                    <p class="text-brand-navy/65 mb-4">{{ __('No booking requests yet.') }}</p>
                    <a href="{{ route('bookings.create') }}" class="btn-gold">{{ __('Make your first booking') }}</a>
                </div>
            @endforelse
        </div>

        @if ($bookings->hasPages())
            <div class="mt-8">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
