<div class="receipt-paper">
    <div class="receipt-center">
        <div class="receipt-brand">ICAN EDUSPACE</div>
        <div>{{ config('app.name', 'ICAN Eduspace') }}</div>
        <div class="receipt-title">{{ $receiptTitle }}</div>
    </div>

    <div class="receipt-divider"></div>

    <div class="receipt-row">
        <span>{{ __("Reference") }}</span>
        <strong>{{ $booking->paymentReference() }}</strong>
    </div>
    <div class="receipt-row">
        <span>{{ __("Booking") }}</span>
        <span>#{{ $booking->id }}</span>
    </div>
    <div class="receipt-row">
        <span>{{ __("Issued") }}</span>
        <span>{{ optional($booking->created_at)->format('M d, Y H:i') }}</span>
    </div>
    <div class="receipt-row">
        <span>{{ __("Printed") }}</span>
        <span>{{ $receiptPrintedAt }}</span>
    </div>

    <div class="receipt-divider"></div>

    <div class="receipt-row">
        <span>{{ __("Customer") }}</span>
        <strong>{{ $booking->customer_name }}</strong>
    </div>
    <div class="receipt-row">
        <span>{{ __("Contact") }}</span>
        <span>{{ $booking->contact }}</span>
    </div>
    @if($booking->organization)
        <div class="receipt-row">
            <span>{{ __("Org") }}</span>
            <span>{{ $booking->organization }}</span>
        </div>
    @endif

    <div class="receipt-divider"></div>

    <div class="receipt-row">
        <span>{{ __("Room") }}</span>
        <span>{{ $booking->classroom?->name ?? __("To be confirmed") }}</span>
    </div>
    <div class="receipt-row">
        <span>{{ __("Date") }}</span>
        <span>{{ optional($booking->starts_at)->format('M d, Y') }}</span>
    </div>
    <div class="receipt-row">
        <span>{{ __("Time") }}</span>
        <span>{{ optional($booking->starts_at)->format('H:i') }}-{{ optional($booking->ends_at)->format('H:i') }}</span>
    </div>
    <div class="receipt-row">
        <span>{{ __("Purpose") }}</span>
        <span>{{ $booking->purpose }}</span>
    </div>

    <div class="receipt-divider"></div>

    <div class="receipt-row">
        <span>{{ __("Total est.") }}</span>
        <span>{{ $pricing['total'] !== null ? \App\Support\Money::format($pricing['total']) : '—' }}</span>
    </div>
    <div class="receipt-row">
        <span>{{ __("Payment type") }}</span>
        <strong>{{ $booking->paymentScopeLabel() }}</strong>
    </div>
    <div class="receipt-amount-total">{{ __("Amount") }}: {{ $selectedAmountLabel }}</div>
    <div class="receipt-row">
        <span>{{ __("Reservation") }}</span>
        <span>{{ $booking->reservation_fee_amount ? \App\Support\Money::format($booking->reservation_fee_amount) : __("Not required") }}</span>
    </div>
    @if($booking->full_payment_paid_at)
        <div class="receipt-paid-stamp">{{ __("Full amount paid") }}</div>
    @elseif($booking->reservation_fee_paid_at)
        <div class="receipt-paid-stamp">{{ __("Reservation paid") }}</div>
    @endif
    <div class="receipt-row">
        <span>{{ __("Method") }}</span>
        <span>{{ $booking->paymentMethodLabel() }}</span>
    </div>
    <div class="receipt-row">
        <span>{{ __("Paid at") }}</span>
        <span>{{ $selectedPaidAt ? $selectedPaidAt->format('M d, Y H:i') : __("Pending") }}</span>
    </div>
    @if($selectedPaidBy)
        <div class="receipt-row">
            <span>{{ __("Staff") }}</span>
            <span>{{ $selectedPaidBy->name }}</span>
        </div>
    @endif

    <div class="receipt-status">{{ $receiptStatus }}</div>

    <div class="receipt-divider"></div>
    <div class="receipt-footer">
        {{ $receiptFooter }}
    </div>
</div>
