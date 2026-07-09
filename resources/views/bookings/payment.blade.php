<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-end gap-4 flex-wrap">
            <div>
                <p class="eyebrow mb-1">{{ __("Booking #").$booking->id }}</p>
                <h1 class="font-serif text-4xl text-brand-navy">{{ __("Counter payment") }}</h1>
                <p class="text-brand-navy/60 mt-2 max-w-2xl">{{ __("This kiosk creates a counter payment slip. Choose reservation-only or full payment, pay cash at the counter, then staff marks the receipt paid.") }}</p>
            </div>
            @php
                $selectedPaidAt = $booking->selectedPaymentPaidAt();
                $selectedAmount = $booking->counterAmountDue();
            @endphp
            <span class="status-badge {{ $selectedPaidAt ? 'status-approved' : ($selectedAmount ? 'status-pending' : 'status-cancelled') }}">{{ $booking->selectedPaymentStatusLabel() }}</span>
        </div>
    </x-slot>

    @php
        $currentScope = old('payment_scope', $booking->paymentScopeFormValue());
        $selectedPaidAt = $booking->selectedPaymentPaidAt();
        $selectedAmount = $booking->counterAmountDue();
        $selectedPaidBy = $booking->isFullPaymentSelected() ? $booking->fullPaymentMarkedPaidBy : $booking->reservationFeeMarkedPaidBy;
        $selectedAmountLabel = $selectedAmount !== null && $selectedAmount > 0 ? \App\Support\Money::format($selectedAmount) : __('Not required');
        $reservationPercentLabel = rtrim(rtrim((string) $booking->reservation_fee_percent, '0'), '.');
        $paymentScopeNote = $booking->isFullPaymentSelected()
            ? __('Full payment')
            : ($booking->reservation_fee_amount ? __('Reservation').' · '.$reservationPercentLabel.'%' : __('Reservation not required'));
        $scopeDetails = [
            \App\Models\Booking::PAYMENT_SCOPE_RESERVATION => [
                'amount' => $booking->reservation_fee_amount ? \App\Support\Money::format($booking->reservation_fee_amount) : __('Not required'),
                'description' => __('Pay only the reservation fee now. Staff can collect the remaining balance later.'),
                'note' => __('Downpayment / reservation'),
            ],
            \App\Models\Booking::PAYMENT_SCOPE_FULL => [
                'amount' => $booking->estimated_price !== null ? \App\Support\Money::format($booking->estimated_price) : __('Not available'),
                'description' => __('Pay the full estimated booking amount in cash at the counter.'),
                'note' => __('Full counter payment'),
            ],
        ];
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
        .payment-choice {
            display: block;
            height: 100%;
            position: relative;
        }

        .payment-choice input {
            opacity: 0;
            position: absolute;
            pointer-events: none;
        }

        .payment-choice-panel {
            align-items: flex-start;
            background: #fff;
            border: 1px solid rgba(13, 28, 76, 0.16);
            color: #0D1C4C;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            height: 100%;
            min-height: 12rem;
            padding: 1rem;
            transition: background 180ms ease, border-color 180ms ease, box-shadow 180ms ease;
        }

        .payment-choice-panel:hover {
            border-color: rgba(217, 167, 47, 0.82);
            background: rgba(217, 167, 47, 0.04);
        }

        .payment-choice input:checked + .payment-choice-panel {
            background: rgba(217, 167, 47, 0.09);
            border-color: #D9A72F;
            box-shadow: inset 0 0 0 1px #D9A72F;
        }

        .payment-choice input:focus-visible + .payment-choice-panel {
            outline: 2px solid #D9A72F;
            outline-offset: 2px;
        }

        .payment-choice-indicator {
            align-items: center;
            border: 1px solid rgba(13, 28, 76, 0.28);
            display: inline-flex;
            height: 1.15rem;
            justify-content: center;
            margin-top: 0.2rem;
            width: 1.15rem;
        }

        .payment-choice-indicator::after {
            background: transparent;
            content: "";
            display: block;
            height: 0.55rem;
            width: 0.55rem;
        }

        .payment-choice input:checked + .payment-choice-panel .payment-choice-indicator {
            background: #D9A72F;
            border-color: #D9A72F;
        }

        .payment-choice input:checked + .payment-choice-panel .payment-choice-indicator::after {
            background: #07112F;
        }

        .payment-choice input:checked + .payment-choice-panel .payment-choice-title {
            color: #0D1C4C;
        }

        .receipt-print-area {
            display: none;
        }

        .paid-amount-card {
            background: rgba(217, 167, 47, 0.18);
            border-color: rgba(217, 167, 47, 0.75);
            box-shadow: inset 0 0 0 1px rgba(217, 167, 47, 0.35), 0 4px 18px rgba(7,17,47,0.04);
        }

        .paid-amount-label {
            background: #D9A72F;
            color: #07112F;
            display: inline-flex;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            margin-top: 0.85rem;
            padding: 0.28rem 0.55rem;
            text-transform: uppercase;
        }

        @media print {
            @page {
                size: auto;
                margin: 0;
            }

            html,
            body {
                background: #fff !important;
                color: #000 !important;
                height: auto !important;
                margin: 0 !important;
                min-height: 0 !important;
                overflow: hidden !important;
                padding: 0 !important;
                width: 100% !important;
            }

            body > :not(.receipt-print-area) {
                display: none !important;
            }

            .receipt-print-area,
            .receipt-print-area * {
                visibility: visible !important;
            }

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
                box-sizing: border-box;
                display: block !important;
                font-family: Arial, sans-serif !important;
                height: var(--receipt-print-height);
                margin: 0 !important;
                max-height: var(--receipt-print-height);
                min-height: var(--receipt-print-height);
                overflow: hidden !important;
                page-break-after: avoid;
                padding: 0 !important;
                position: static;
                width: 100% !important;
            }

            .receipt-paper {
                break-inside: avoid;
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

            .receipt-center {
                text-align: center;
            }

            .receipt-brand {
                font-size: calc(var(--receipt-brand-font) * var(--receipt-scale));
                font-weight: 700;
                letter-spacing: 0.02em;
            }

            .receipt-title {
                font-size: calc(var(--receipt-title-font) * var(--receipt-scale));
                font-weight: 700;
                letter-spacing: 0.02em;
                margin-top: calc(min(clamp(1px, 0.22vmin, 3px), clamp(1px, 0.16vh, 2px)) * var(--receipt-scale));
                text-transform: uppercase;
            }

            .receipt-divider {
                border-top: var(--receipt-border-width) dashed #000;
                margin: calc(var(--receipt-divider-margin) * var(--receipt-scale)) 0;
            }

            .receipt-row {
                display: flex;
                justify-content: space-between;
                gap: calc(var(--receipt-row-gap) * var(--receipt-scale));
                margin: calc(var(--receipt-row-margin) * var(--receipt-scale)) 0;
            }

            .receipt-row span:first-child {
                flex: 0 0 auto;
            }

            .receipt-row span:last-child,
            .receipt-row strong {
                overflow-wrap: anywhere;
                text-align: right;
            }

            .receipt-status {
                border: var(--receipt-border-width) solid #000;
                font-size: calc(var(--receipt-status-font) * var(--receipt-scale));
                font-weight: 700;
                letter-spacing: 0.02em;
                margin-top: calc(var(--receipt-box-margin) * var(--receipt-scale));
                padding: calc(var(--receipt-box-padding) * var(--receipt-scale));
                text-align: center;
                text-transform: uppercase;
            }

            .receipt-paid-stamp {
                background: #D9A72F !important;
                border: var(--receipt-border-width) solid #000;
                color: #000 !important;
                font-size: calc(var(--receipt-status-font) * var(--receipt-scale));
                font-weight: 700;
                letter-spacing: 0.04em;
                margin: calc(var(--receipt-box-margin) * var(--receipt-scale)) 0;
                padding: calc(var(--receipt-box-padding) * var(--receipt-scale));
                text-align: center;
                text-transform: uppercase;
            }

            .receipt-amount-total {
                border: var(--receipt-border-width) solid #000;
                font-size: calc(var(--receipt-amount-font) * var(--receipt-scale));
                font-weight: 700;
                margin: calc(var(--receipt-box-margin) * var(--receipt-scale)) 0;
                overflow-wrap: anywhere;
                padding: calc(var(--receipt-box-padding) * var(--receipt-scale));
                text-align: center;
            }

            .receipt-footer {
                font-size: calc(var(--receipt-footer-font) * var(--receipt-scale));
                margin-top: calc(var(--receipt-box-margin) * var(--receipt-scale));
                text-align: center;
            }
	        }

            @media print and (max-width: 90mm) {
                .receipt-print-area {
                    --receipt-body-font: min(clamp(11pt, 3vmin, 15pt), clamp(10pt, 2.1vh, 13pt));
                    --receipt-brand-font: min(clamp(20pt, 5.5vmin, 30pt), clamp(18pt, 3.7vh, 25pt));
                    --receipt-title-font: min(clamp(14pt, 3.8vmin, 20pt), clamp(12pt, 2.5vh, 17pt));
                    --receipt-status-font: min(clamp(14pt, 3.8vmin, 20pt), clamp(12pt, 2.5vh, 17pt));
                    --receipt-amount-font: min(clamp(17pt, 4.6vmin, 25pt), clamp(15pt, 3vh, 21pt));
                    --receipt-footer-font: min(clamp(9pt, 1.9vmin, 11.5pt), clamp(8pt, 1.35vh, 10pt));
                    --receipt-page-padding: min(clamp(2.5mm, 1.4vmin, 5mm), clamp(2mm, 0.9vh, 4mm));
                    --receipt-divider-margin: min(clamp(3px, 0.75vmin, 7px), clamp(2px, 0.45vh, 5px));
                    --receipt-row-gap: min(clamp(5px, 1vmin, 10px), clamp(4px, 0.7vh, 8px));
                    --receipt-box-margin: min(clamp(4px, 0.8vmin, 8px), clamp(3px, 0.55vh, 6px));
                    --receipt-box-padding: min(clamp(5px, 1vmin, 10px), clamp(4px, 0.7vh, 8px));
                }

                .receipt-row {
                    display: block;
                }

                .receipt-row span:first-child {
                    display: block;
                    font-weight: 700;
                }

                .receipt-row span:last-child,
                .receipt-row strong {
                    display: block;
                    text-align: left;
                }
            }

            @media print and (max-height: 220mm) {
                .receipt-print-area {
                    --receipt-body-font: min(clamp(11pt, 3vmin, 15pt), clamp(10pt, 1.9vh, 12.5pt));
                    --receipt-brand-font: min(clamp(20pt, 5.4vmin, 30pt), clamp(18pt, 3.3vh, 24pt));
                    --receipt-title-font: min(clamp(14pt, 3.8vmin, 20pt), clamp(12pt, 2.25vh, 16pt));
                    --receipt-status-font: min(clamp(14pt, 3.8vmin, 20pt), clamp(12pt, 2.25vh, 16pt));
                    --receipt-amount-font: min(clamp(17pt, 4.5vmin, 25pt), clamp(15pt, 2.8vh, 20pt));
                    --receipt-footer-font: min(clamp(9pt, 1.85vmin, 11pt), clamp(8pt, 1.2vh, 9.5pt));
                    --receipt-page-padding: min(clamp(2.5mm, 1.35vmin, 5mm), clamp(2mm, 0.8vh, 3.5mm));
                    --receipt-divider-margin: min(clamp(3px, 0.7vmin, 7px), clamp(2px, 0.4vh, 5px));
                    --receipt-box-margin: min(clamp(4px, 0.8vmin, 8px), clamp(3px, 0.5vh, 6px));
                    --receipt-box-padding: min(clamp(5px, 1vmin, 10px), clamp(4px, 0.65vh, 8px));
                }
            }

            @media print and (max-height: 160mm) {
                .receipt-print-area {
                    --receipt-body-font: min(clamp(9pt, 2.4vmin, 12pt), clamp(8pt, 1.55vh, 10.5pt));
                    --receipt-brand-font: min(clamp(16pt, 4vmin, 22pt), clamp(14pt, 2.6vh, 18pt));
                    --receipt-title-font: min(clamp(11pt, 2.9vmin, 16pt), clamp(10pt, 1.85vh, 13pt));
                    --receipt-status-font: min(clamp(11pt, 2.9vmin, 16pt), clamp(10pt, 1.85vh, 13pt));
                    --receipt-amount-font: min(clamp(14pt, 3.4vmin, 20pt), clamp(12pt, 2.2vh, 16pt));
                    --receipt-footer-font: min(clamp(7.5pt, 1.6vmin, 9.5pt), clamp(7pt, 1vh, 8pt));
                }
            }

            @media print and (max-height: 120mm) {
                .receipt-print-area {
                    --receipt-body-font: min(clamp(8pt, 2vmin, 10.5pt), clamp(7.2pt, 1.25vh, 8.8pt));
                    --receipt-brand-font: min(clamp(13pt, 3.2vmin, 18pt), clamp(11pt, 2vh, 14pt));
                    --receipt-title-font: min(clamp(9pt, 2.35vmin, 13pt), clamp(8pt, 1.5vh, 10.5pt));
                    --receipt-status-font: min(clamp(9pt, 2.35vmin, 13pt), clamp(8pt, 1.5vh, 10.5pt));
                    --receipt-amount-font: min(clamp(11pt, 2.8vmin, 16pt), clamp(10pt, 1.8vh, 12.5pt));
                    --receipt-footer-font: min(clamp(6.8pt, 1.35vmin, 8.2pt), clamp(6.2pt, 0.8vh, 7pt));
                }
            }
	    </style>

	    @push('modals')
	        <section class="receipt-print-area" aria-label="{{ $receiptTitle }}">
	            @include('bookings.partials.receipt-paper')
	        </section>
	    @endpush
		
	    <div class="payment-page max-w-4xl mx-auto px-6 py-12 space-y-6">
        @if (session('booking_saved'))
            <div class="border border-emerald-400 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
                {{ session('booking_saved') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="border border-red-400 bg-red-50 px-5 py-4 text-sm text-red-600">
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                        <li>· {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid md:grid-cols-3 gap-4">
            <div class="card p-5 {{ $selectedPaidAt ? 'paid-amount-card' : '' }}">
                <p class="eyebrow mb-2">{{ __("Amount") }}</p>
                <p class="font-serif text-3xl text-brand-navy">{{ $selectedAmountLabel }}</p>
                <p class="mt-2 text-sm text-brand-navy/55">{{ $paymentScopeNote }}</p>
                @if($selectedPaidAt)
                    <span class="paid-amount-label">{{ __("Paid") }}</span>
                @endif
            </div>

            <div class="card p-5">
                <p class="eyebrow mb-2">{{ __("Booking") }}</p>
                <p class="font-serif text-2xl text-brand-navy">{{ optional($booking->starts_at)->format('M d, Y') }}</p>
                <p class="mt-2 text-sm text-brand-navy/60">{{ optional($booking->starts_at)->format('H:i') }} - {{ optional($booking->ends_at)->format('H:i') }}</p>
                <p class="mt-1 text-sm text-brand-navy/45">{{ $booking->classroom?->name ?? __("Room to be confirmed") }}</p>
            </div>

            <div class="card p-5">
                <p class="eyebrow mb-2">{{ __("Payment type") }}</p>
                <p class="font-serif text-2xl text-brand-navy">{{ $booking->paymentScopeLabel() }}</p>
                <p class="mt-2 text-sm text-brand-navy/55">{{ __("Method") }} · {{ $booking->paymentMethodLabel() }}</p>
            </div>
        </div>

        <div class="card p-6">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-5">
                <div>
                    <p class="eyebrow mb-2">{{ $receiptTitle }}</p>
                    <p class="font-serif text-3xl text-brand-navy">{{ $booking->paymentReference() }}</p>
                    <p class="mt-2 text-sm text-brand-navy/60">{{ __("Show this reference at the counter before paying.") }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('bookings.receipt.show', $booking) }}" class="btn-ghost">{{ __("Receipt preview") }}</a>
                    <button type="button" class="btn-ghost" onclick="window.print()">{{ $selectedPaidAt ? __("Print receipt") : __("Print slip") }}</button>
                </div>
            </div>

            <div class="mt-6 grid md:grid-cols-2 gap-4 text-sm text-brand-navy/70">
                <div class="border border-brand-navy/10 px-4 py-3">
                    <span class="text-xs uppercase tracking-[0.18em] text-brand-navy/40">{{ __("Status") }}</span>
                    <p class="mt-1 font-medium text-brand-navy">{{ $receiptStatus }}</p>
                </div>
                <div class="border border-brand-navy/10 px-4 py-3">
                    <span class="text-xs uppercase tracking-[0.18em] text-brand-navy/40">{{ __("Payment type") }}</span>
                    <p class="mt-1 font-medium text-brand-navy">{{ $booking->paymentScopeLabel() }}</p>
                </div>
                <div class="border border-brand-navy/10 px-4 py-3">
                    <span class="text-xs uppercase tracking-[0.18em] text-brand-navy/40">{{ __("Amount") }}</span>
                    <p class="mt-1 font-medium text-brand-navy">{{ $selectedAmountLabel }}</p>
                </div>
                <div class="border border-brand-navy/10 px-4 py-3">
                    <span class="text-xs uppercase tracking-[0.18em] text-brand-navy/40">{{ __("Paid at") }}</span>
                    <p class="mt-1 font-medium text-brand-navy">{{ $selectedPaidAt ? $selectedPaidAt->format('M d, Y H:i') : __("Pending staff confirmation") }}</p>
                </div>
            </div>
        </div>

        @can('payment', $booking)
            <form method="POST" action="{{ route('bookings.payment.update', $booking) }}" class="card p-6 space-y-6">
                @csrf
                <input type="hidden" name="payment_method" value="cash">
                <div>
                    <p class="eyebrow mb-2">{{ __("Payment amount") }}</p>
                    <div class="grid md:grid-cols-2 gap-3">
                        @foreach(\App\Models\Booking::PAYMENT_SCOPE_OPTIONS as $value => $label)
                            <label class="payment-choice" data-payment-choice="{{ $value }}">
                                <input type="radio" name="payment_scope" value="{{ $value }}" required @checked($currentScope === $value)>
                                <span class="payment-choice-panel">
                                    <span class="flex items-start justify-between gap-3 w-full">
                                        <span class="payment-choice-title block font-serif text-2xl text-brand-navy">{{ __($label) }}</span>
                                        <span class="payment-choice-indicator" aria-hidden="true"></span>
                                    </span>
                                    <span class="block font-medium text-brand-navy">{{ $scopeDetails[$value]['amount'] }}</span>
                                    <span class="block text-sm text-brand-navy/60">{{ $scopeDetails[$value]['description'] }}</span>
                                    <span class="inline-flex mt-auto text-xs uppercase tracking-[0.18em] text-brand-navy/45">{{ $scopeDetails[$value]['note'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="border border-brand-navy/10 bg-brand-navy/[0.02] px-5 py-4">
                    <p class="eyebrow mb-2">{{ __("Counter instructions") }}</p>
                    <p class="text-sm text-brand-navy/65">{{ __("Payment method") }}: {{ \App\Models\Booking::PAYMENT_METHOD_OPTIONS['cash'] }}</p>
                    <p class="mt-2 text-sm text-brand-navy/65">{{ __("Save this slip, go to the counter, pay cash, and wait for staff to mark the receipt paid.") }}</p>
                    @if($paymentInstructions)
                        <p class="mt-3 text-sm text-brand-navy/65 whitespace-pre-line">{{ $paymentInstructions }}</p>
                    @endif
                </div>

                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <a href="{{ route('bookings.show', $booking) }}" class="btn-ghost w-full md:w-auto text-center">{{ __("Back") }}</a>
                    <button type="submit" class="btn-gold w-full md:w-auto">{{ __("Create counter slip") }}</button>
                </div>
            </form>
        @else
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <a href="{{ route('bookings.show', $booking) }}" class="btn-ghost w-full md:w-auto text-center">{{ __("Back") }}</a>
            </div>
        @endcan
    </div>
</x-app-layout>
