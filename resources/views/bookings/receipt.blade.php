<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-end gap-4 flex-wrap">
            <div>
                <p class="eyebrow mb-1">{{ __("Booking #").$booking->id }}</p>
                <h1 class="font-serif text-4xl text-brand-navy">{{ __("Receipt preview") }}</h1>
                <p class="text-brand-navy/60 mt-2 max-w-2xl">{{ __("Review the receipt exactly before printing or sharing it with staff.") }}</p>
            </div>
            <span class="status-badge {{ $booking->selectedPaymentPaidAt() ? 'status-approved' : 'status-pending' }}">{{ $booking->selectedPaymentStatusLabel() }}</span>
        </div>
    </x-slot>

    @php
        $selectedPaidAt = $booking->selectedPaymentPaidAt();
        $selectedAmount = $booking->counterAmountDue();
        $selectedPaidBy = $booking->isFullPaymentSelected() ? $booking->fullPaymentMarkedPaidBy : $booking->reservationFeeMarkedPaidBy;
        $selectedAmountLabel = $selectedAmount !== null && $selectedAmount > 0 ? \App\Support\Money::format($selectedAmount) : __('Not required');
        $receiptTitle = $selectedPaidAt ? __('Paid receipt') : __('Counter payment slip');
        $receiptStatus = $booking->isFullPaymentSelected()
            ? ($booking->full_payment_paid_at ? __('Fully paid') : __('Awaiting full payment'))
            : ($booking->reservation_fee_paid_at ? __('Reservation paid') : __('Awaiting counter payment'));
        $receiptFooter = $selectedPaidAt
            ? __('This receipt confirms payment recorded by ICAN staff.')
            : __('Present this slip to ICAN staff and pay cash at the counter.');
        $receiptPrintedAt = now()->format('M d, Y H:i');
    @endphp

    <style>
        .receipt-print-area {
            display: none;
        }

        .receipt-preview-shell {
            background: #fff;
            border: 1px solid rgba(13, 28, 76, 0.12);
            box-shadow: 0 16px 42px rgba(7, 17, 47, 0.08);
            margin: 0 auto;
            max-width: 620px;
        }

        .receipt-paper {
            color: #07112F;
            font-family: Arial, sans-serif;
            font-size: 1rem;
            line-height: 1.35;
            padding: 1.5rem;
        }

        .receipt-center { text-align: center; }
        .receipt-brand { font-size: 1.8rem; font-weight: 700; letter-spacing: 0.02em; }
        .receipt-title { font-size: 1.15rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
        .receipt-divider { border-top: 1px dashed rgba(7, 17, 47, 0.5); margin: 0.75rem 0; }
        .receipt-row { display: flex; justify-content: space-between; gap: 1rem; margin: 0.28rem 0; }
        .receipt-row span:last-child, .receipt-row strong { overflow-wrap: anywhere; text-align: right; }
        .receipt-amount-total, .receipt-status, .receipt-paid-stamp {
            border: 1px solid #07112F;
            font-weight: 700;
            margin: 0.75rem 0;
            padding: 0.75rem;
            text-align: center;
        }
        .receipt-amount-total { font-size: 1.4rem; }
        .receipt-paid-stamp { background: #D9A72F; }
        .receipt-footer { margin-top: 0.75rem; text-align: center; }

        @media print {
            @page { size: auto; margin: 0; }
            html, body {
                background: #fff !important;
                color: #000 !important;
                margin: 0 !important;
                min-height: 0 !important;
                overflow: hidden !important;
                padding: 0 !important;
                width: 100% !important;
            }
            body > :not(.receipt-print-area) { display: none !important; }
            .receipt-print-area, .receipt-print-area * { visibility: visible !important; }
            .receipt-print-area {
                --receipt-scale: 0.9;
                --receipt-border-width: 1.8px;
                --receipt-print-height: 100vh;
                --receipt-body-font: min(clamp(14pt, 4vmin, 22pt), clamp(13pt, 2.8vh, 18pt));
                --receipt-brand-font: min(clamp(28pt, 8vmin, 48pt), clamp(24pt, 5vh, 38pt));
                --receipt-title-font: min(clamp(18pt, 5vmin, 32pt), clamp(16pt, 3.4vh, 26pt));
                --receipt-status-font: min(clamp(18pt, 5vmin, 32pt), clamp(16pt, 3.4vh, 26pt));
                --receipt-amount-font: min(clamp(24pt, 6.5vmin, 42pt), clamp(22pt, 4.5vh, 34pt));
                --receipt-footer-font: min(clamp(11pt, 2.4vmin, 16pt), clamp(10pt, 1.8vh, 13.5pt));
                --receipt-page-padding: min(clamp(4mm, 2.25vmin, 8mm), clamp(3mm, 1.45vh, 6mm));
                --receipt-divider-margin: min(clamp(5px, 1.1vmin, 10px), clamp(4px, 0.7vh, 8px));
                --receipt-row-gap: min(clamp(8px, 1.6vmin, 16px), clamp(6px, 1vh, 12px));
                --receipt-row-margin: min(clamp(3px, 0.6vmin, 6px), clamp(2px, 0.4vh, 5px));
                --receipt-box-margin: min(clamp(6px, 1.3vmin, 12px), clamp(4px, 0.85vh, 10px));
                --receipt-box-padding: min(clamp(7px, 1.5vmin, 14px), clamp(5px, 1vh, 12px));
                break-after: avoid;
                display: block !important;
                font-family: Arial, sans-serif !important;
                height: var(--receipt-print-height);
                max-height: var(--receipt-print-height);
                overflow: hidden !important;
                page-break-after: avoid;
                width: 100% !important;
            }
            .receipt-print-area .receipt-paper {
                box-sizing: border-box;
                color: #000;
                font-size: calc(var(--receipt-body-font) * var(--receipt-scale));
                line-height: 1.12;
                max-height: var(--receipt-print-height);
                overflow: hidden;
                padding: calc(var(--receipt-page-padding) * var(--receipt-scale));
                page-break-inside: avoid;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                width: 100%;
            }
            .receipt-print-area .receipt-brand { font-size: calc(var(--receipt-brand-font) * var(--receipt-scale)); }
            .receipt-print-area .receipt-title { font-size: calc(var(--receipt-title-font) * var(--receipt-scale)); }
            .receipt-print-area .receipt-divider { border-top: var(--receipt-border-width) dashed #000; margin: calc(var(--receipt-divider-margin) * var(--receipt-scale)) 0; }
            .receipt-print-area .receipt-row { gap: calc(var(--receipt-row-gap) * var(--receipt-scale)); margin: calc(var(--receipt-row-margin) * var(--receipt-scale)) 0; }
            .receipt-print-area .receipt-status, .receipt-print-area .receipt-paid-stamp, .receipt-print-area .receipt-amount-total {
                border: var(--receipt-border-width) solid #000;
                margin: calc(var(--receipt-box-margin) * var(--receipt-scale)) 0;
                padding: calc(var(--receipt-box-padding) * var(--receipt-scale));
            }
            .receipt-print-area .receipt-status, .receipt-print-area .receipt-paid-stamp { font-size: calc(var(--receipt-status-font) * var(--receipt-scale)); }
            .receipt-print-area .receipt-amount-total { font-size: calc(var(--receipt-amount-font) * var(--receipt-scale)); }
            .receipt-print-area .receipt-footer { font-size: calc(var(--receipt-footer-font) * var(--receipt-scale)); margin-top: calc(var(--receipt-box-margin) * var(--receipt-scale)); }
        }
    </style>

    @push('modals')
        <section class="receipt-print-area" aria-label="{{ $receiptTitle }}">
            @include('bookings.partials.receipt-paper')
        </section>
    @endpush

    <div class="max-w-5xl mx-auto px-6 py-12 space-y-6">
        <div class="flex flex-wrap justify-between gap-3">
            <a href="{{ route('bookings.payment.edit', $booking) }}" class="btn-ghost">← {{ __("Back to payment") }}</a>
            <button type="button" class="btn-gold" onclick="window.print()">{{ $selectedPaidAt ? __("Print receipt") : __("Print slip") }}</button>
        </div>

        <div class="receipt-preview-shell">
            @include('bookings.partials.receipt-paper')
        </div>
    </div>
</x-app-layout>
