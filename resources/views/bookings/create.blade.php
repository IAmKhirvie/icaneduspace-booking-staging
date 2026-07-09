<x-app-layout>
    <x-slot name="header">
        <p class="eyebrow mb-1">{{ __("Booking request") }}</p>
        <h1 class="font-serif text-4xl text-brand-navy">{{ __("Reserve a classroom") }}</h1>
        <p class="text-brand-navy/60 mt-2 max-w-2xl">{{ __("Tell us when you need the room. Staff reviews availability before final confirmation.") }}</p>
    </x-slot>

    <div class="max-w-3xl mx-auto px-6 py-12">
        @if ($errors->any())
            <div class="mb-6 border border-red-400 bg-red-50 px-5 py-4 text-sm text-red-600">
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                        <li>· {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6">
            <p class="eyebrow mb-3">{{ __("Choose a room") }}</p>
            <style>.room-card input:checked ~ .ring-mark{outline:2px solid #D9A72F;outline-offset:-2px;}</style>
            <div class="grid md:grid-cols-3 gap-4">
                @foreach ($classrooms as $classroom)
                    <label class="room-card cursor-pointer block">
                        <input type="radio" name="classroom_id" value="{{ $classroom->id }}" class="sr-only" form="booking-form" @checked(old('classroom_id', request('room')) == $classroom->id)>
                        <span class="ring-mark absolute inset-0 z-20 pointer-events-none"></span>
                        <div class="img-bg" style="background-image:url('{{ $classroom->hero_image }}')"></div>
                        <div class="scrim"></div>
                        <div class="relative z-10 p-5 h-full min-h-[200px] flex flex-col justify-end">
                            <p class="text-white/70 text-xs uppercase tracking-[0.18em] mb-1">{{ $classroom->location }}</p>
                            <h3 class="font-serif text-xl text-white">{{ $classroom->name }}</h3>
                            <p class="text-white/70 text-xs mt-1">{{ $classroom->capacity }} {{ __("seats") }} · {{ \App\Support\Money::format($classroom->hourly_rate) }}/hr</p>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <form id="booking-form" method="POST" action="{{ route('bookings.store') }}" class="card p-8 grid grid-cols-1 md:grid-cols-2 gap-5" data-turnstile-booking-form>
            @csrf
            <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-10000px;width:1px;height:1px;opacity:0;">
            <input type="hidden" id="confirm_additional_booking" name="confirm_additional_booking" value="{{ old('confirm_additional_booking', '0') }}">


            <div class="md:col-span-2">
                <label class="block mb-2 text-xs uppercase tracking-[0.22em] text-brand-navy/60" for="purpose">{{ __("Purpose") }}</label>
                <input id="purpose" name="purpose" type="text" required value="{{ old('purpose') }}" placeholder="AI lecture, mentoring, workshop…">
            </div>
            <div>
                <label class="block mb-2 text-xs uppercase tracking-[0.22em] text-brand-navy/60" for="service_package_id">{{ __("Package") }}</label>
                <select id="service_package_id" name="service_package_id">
                    <option value="">— None —</option>
                    @foreach ($packages as $package)
                        <option value="{{ $package->id }}" @selected(old('service_package_id') == $package->id)>{{ $package->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-2 text-xs uppercase tracking-[0.22em] text-brand-navy/60" for="booking_date">{{ __("Date") }}</label>
                <input id="booking_date" name="booking_date" type="date" required min="{{ now()->toDateString() }}" value="{{ old('booking_date') }}">
            </div>
            <div>
                <label class="block mb-2 text-xs uppercase tracking-[0.22em] text-brand-navy/60" for="booking_end_date">{{ __("End date") }} <span class="text-brand-navy/35">{{ __("(optional)") }}</span></label>
                <input id="booking_end_date" name="booking_end_date" type="date" min="{{ now()->toDateString() }}" value="{{ old('booking_end_date') }}">
            </div>
            <div>
                <label class="block mb-2 text-xs uppercase tracking-[0.22em] text-brand-navy/60" for="time_block">{{ __("Time block") }}</label>
                <select id="time_block" name="time_block" required>
                    <option value="morning" @selected(old('time_block') === 'morning')>{{ __('Morning') }} · 09:00–12:00</option>
                    <option value="afternoon" @selected(old('time_block') === 'afternoon')>{{ __('Afternoon') }} · 13:00–17:00</option>
                    <option value="evening" @selected(old('time_block') === 'evening')>{{ __('Evening') }} · 18:00–21:00</option>
                </select>
            </div>
            <div>
                <label class="block mb-2 text-xs uppercase tracking-[0.22em] text-brand-navy/60" for="participant_count">{{ __("Participants") }}</label>
                <input id="participant_count" name="participant_count" type="number" min="1" max="200" value="{{ old('participant_count') }}" placeholder="12">
            </div>
            <div>
                <label class="block mb-2 text-xs uppercase tracking-[0.22em] text-brand-navy/60" for="format">{{ __("Format") }}</label>
                <select id="format" name="format" required>
                    <option value="Offline">{{ __('Offline') }}</option>
                    <option value="Online broadcast">{{ __('Online broadcast') }}</option>
                    <option value="Hybrid">{{ __('Hybrid') }}</option>
                </select>
            </div>
            <div class="md:col-span-2 border border-brand-navy/10 bg-brand-navy/[0.02] px-5 py-4">
                <p class="block mb-3 text-xs uppercase tracking-[0.22em] text-brand-navy/60">{{ __("Equipment") }}</p>
                <div class="grid sm:grid-cols-2 gap-3">
                    @foreach(\App\Models\Booking::EQUIPMENT_OPTIONS as $value => $label)
                        <label class="flex items-center gap-3 text-sm text-brand-navy/75">
                            <input type="checkbox" name="equipment_requests[]" value="{{ $value }}" @checked(in_array($value, old('equipment_requests', []), true))>
                            <span>{{ __($label) }}</span>
                        </label>
                    @endforeach
                </div>
                <label class="block mt-4 mb-2 text-xs uppercase tracking-[0.22em] text-brand-navy/45" for="equipment_notes">{{ __("Equipment notes") }}</label>
                <input id="equipment_notes" name="equipment_notes" type="text" value="{{ old('equipment_notes') }}" placeholder="{{ __('Example: Need school AI tools prepared for class demo') }}">
            </div>
            <div class="md:col-span-2 border border-brand-navy/10 bg-brand-navy/[0.02] px-5 py-4">
                <p class="block mb-3 text-xs uppercase tracking-[0.22em] text-brand-navy/60">{{ __("Coffee and snacks") }}</p>
                <div class="grid sm:grid-cols-2 gap-3">
                    @foreach(\App\Models\Booking::SNACK_BEVERAGE_OPTIONS as $value => $label)
                        <label class="flex items-center gap-3 text-sm text-brand-navy/75">
                            <input type="checkbox" name="snack_beverage_requests[]" value="{{ $value }}" @checked(in_array($value, old('snack_beverage_requests', []), true))>
                            <span>{{ __($label) }}</span>
                        </label>
                    @endforeach
                </div>
                <label class="block mt-4 mb-2 text-xs uppercase tracking-[0.22em] text-brand-navy/45" for="snack_beverage_notes">{{ __("Coffee/snack notes") }}</label>
                <input id="snack_beverage_notes" name="snack_beverage_notes" type="text" value="{{ old('snack_beverage_notes') }}" placeholder="{{ __('Example: Coffee for 8 guests, no sugar') }}">
            </div>
            <div>
                <label class="block mb-2 text-xs uppercase tracking-[0.22em] text-brand-navy/60" for="customer_name">{{ __("Name") }}</label>
                <input id="customer_name" name="customer_name" type="text" required value="{{ old('customer_name', auth()->user()->name) }}">
            </div>
            <div>
                <label class="block mb-2 text-xs uppercase tracking-[0.22em] text-brand-navy/60" for="contact">{{ __("Contact") }}</label>
                <input id="contact" name="contact" type="text" required value="{{ old('contact', auth()->user()->email) }}" placeholder="Email, phone, or KakaoTalk">
            </div>
            <div>
                <label class="block mb-2 text-xs uppercase tracking-[0.22em] text-brand-navy/60" for="payment_method">{{ __("Payment method") }}</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="">{{ __("Choose payment method") }}</option>
                    @foreach(\App\Models\Booking::PAYMENT_METHOD_OPTIONS as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-2 text-xs uppercase tracking-[0.22em] text-brand-navy/60" for="organization">{{ __("Organization (optional)") }}</label>
                <input id="organization" name="organization" type="text" value="{{ old('organization') }}">
            </div>
            <div class="md:col-span-2">
                <label class="block mb-2 text-xs uppercase tracking-[0.22em] text-brand-navy/60" for="customer_notes">{{ __("Notes") }}</label>
                <textarea id="customer_notes" name="customer_notes" rows="3" placeholder="Anything we should know…">{{ old('customer_notes') }}</textarea>
            </div>
            <div id="additional-booking-warning" class="md:col-span-2 hidden border border-brand-gold/60 bg-brand-gold/10 px-5 py-4 text-sm text-brand-navy">
                <p class="font-medium">{{ __("You already have an active booking for this room on this date.") }}</p>
                <p class="mt-1 text-brand-navy/65">{{ __("This is a different time slot. Staff will review it as a special case before confirmation.") }}</p>
            </div>
            <div class="md:col-span-2 border border-brand-gold/40 bg-brand-gold/10 px-5 py-4" data-pricing-estimate>
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-brand-navy/45 uppercase text-xs tracking-[0.18em]">{{ __("Pricing estimate") }}</p>
                        <p class="font-serif text-3xl text-brand-navy mt-1" data-estimate-total>—</p>
                    </div>
                    <p class="text-brand-navy/50 text-xs uppercase tracking-[0.18em]" data-estimate-hours>—</p>
                </div>
	                <div class="mt-3 grid gap-2 text-sm text-brand-navy/70 sm:grid-cols-2">
	                    <p data-estimate-room>{{ __("Choose a room to calculate the room cost.") }}</p>
	                    <p data-estimate-package>{{ __("Package") }}: {{ __("None") }}</p>
	                </div>
	                <p class="mt-3 text-sm text-brand-navy/70" data-estimate-discount>{{ __("Discount") }}: —</p>
	                <p class="mt-3 text-sm text-brand-navy/70" data-estimate-reservation>{{ __("Reservation fee") }}: —</p>
                @if(!empty($bookingSettings['payment_instructions']))
                    <p class="mt-3 text-sm text-brand-navy/60 whitespace-pre-line">{{ $bookingSettings['payment_instructions'] }}</p>
                @endif
                <p class="mt-3 text-xs uppercase tracking-[0.18em] text-brand-navy/40">{{ __("Final pricing is confirmed after staff review.") }}</p>
            </div>
            @if (app(\App\Services\TurnstileVerifier::class)->enabled())
                <div class="md:col-span-2 flex flex-col items-center">
                    <x-turnstile
                        data-turnstile-widget="booking-create"
                        data-appearance="always"
                        data-callback="bookingTurnstileCallback"
                        data-expired-callback="bookingTurnstileExpiredCallback"
                        data-error-callback="bookingTurnstileErrorCallback"
                    />
                    <p class="mt-2 text-sm text-brand-navy/55 hidden" data-turnstile-status>{{ __("Verification is required before sending.") }}</p>
                    @error('cf-turnstile-response')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif
            <div class="md:col-span-2 flex flex-col md:flex-row justify-between items-center gap-4 pt-2">
                <p class="text-xs uppercase tracking-[0.22em] text-brand-navy/45">{{ __("Dates are confirmed after staff review.") }}</p>
                <button type="submit" class="btn-gold w-full md:w-auto" data-turnstile-submit>{{ __("Send request") }}</button>
            </div>
        </form>
    </div>

    <script>
        (() => {
            const form = document.getElementById('booking-form');
            const confirmation = document.getElementById('confirm_additional_booking');
            const warning = document.getElementById('additional-booking-warning');
            const existingSlots = @json($existingBookingSlots ?? []);
            const turnstileEnabled = @json(app(\App\Services\TurnstileVerifier::class)->enabled());
            const turnstileSelector = '[data-turnstile-widget="booking-create"]';
            const turnstileWidget = form.querySelector(turnstileSelector);
            const turnstileStatus = form.querySelector('[data-turnstile-status]');
            const submitButton = form.querySelector('[data-turnstile-submit]');
            const estimateTotal = form.querySelector('[data-estimate-total]');
            const estimateHours = form.querySelector('[data-estimate-hours]');
	            const estimateRoom = form.querySelector('[data-estimate-room]');
	            const estimatePackage = form.querySelector('[data-estimate-package]');
	            const estimateDiscount = form.querySelector('[data-estimate-discount]');
	            const estimateReservation = form.querySelector('[data-estimate-reservation]');
	            const reservationFeePercent = Math.min(@json(\App\Models\BookingSetting::RESERVATION_FEE_MAX_PERCENT), Math.max(0, Number(@json($bookingSettings['reservation_fee_percent'] ?? 0))));
	            const specialDiscountPercent = Math.min(100, Math.max(0, Number(@json($bookingSettings['special_discount_percent'] ?? 0))));
            const rooms = @json($classrooms->mapWithKeys(fn ($room) => [$room->id => [
                'name' => $room->name,
                'hourly_rate' => (int) $room->hourly_rate,
            ]]));
            const packages = @json($packages->mapWithKeys(fn ($package) => [$package->id => [
                'name' => $package->name,
                'base_price' => (int) $package->base_price,
            ]]));
            const timeBlocks = {
                morning: 3,
                afternoon: 4,
                evening: 3,
            };
            const money = new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP',
                maximumFractionDigits: 0,
            });

            const selectedRoom = () => document.querySelector('input[name="classroom_id"]:checked')?.value || '';
            const selectedPackage = () => document.getElementById('service_package_id')?.value || '';
            const selectedDate = () => document.getElementById('booking_date')?.value || '';
            const selectedEndDate = () => document.getElementById('booking_end_date')?.value || '';
            const selectedTime = () => document.getElementById('time_block')?.value || '';
            const selectedTimeLabel = () => document.querySelector('#time_block option:checked')?.textContent.trim() || 'selected time';
            const turnstileResponse = () => form.querySelector('input[name="cf-turnstile-response"]')?.value || '';
            const formatHours = (hours) => `${Number.isInteger(hours) ? hours : hours.toFixed(2).replace(/\.?0+$/, '')} hr`;
            const parseDate = (value) => {
                const parts = String(value || '').split('-').map(Number);

                if (parts.length !== 3 || parts.some((part) => Number.isNaN(part))) {
                    return null;
                }

                return new Date(parts[0], parts[1] - 1, parts[2]);
            };
            const dateKey = (date) => [
                date.getFullYear(),
                String(date.getMonth() + 1).padStart(2, '0'),
                String(date.getDate()).padStart(2, '0'),
            ].join('-');
            const rangeDates = () => {
                const start = parseDate(selectedDate());
                const end = parseDate(selectedEndDate()) || start;

                if (!start) {
                    return [];
                }

                if (end < start) {
                    return [dateKey(start)];
                }

                const dates = [];

                for (const date = new Date(start); date <= end; date.setDate(date.getDate() + 1)) {
                    dates.push(dateKey(date));
                }

                return dates;
            };
            const bookingDayCount = () => Math.max(1, rangeDates().length);
            const scheduleLabel = () => {
                const dates = rangeDates();

                if (dates.length > 1) {
                    return `${dates[0]} to ${dates[dates.length - 1]} (${dates.length} days)`;
                }

                return dates[0] || 'the selected date';
            };

            const refreshEstimate = () => {
                const room = rooms[selectedRoom()];
                const servicePackage = packages[selectedPackage()];
                const hoursPerDay = timeBlocks[selectedTime()] || 0;
                const days = bookingDayCount();
                const roomCost = room ? Math.round(room.hourly_rate * hoursPerDay) * days : 0;
                const packageCost = servicePackage ? servicePackage.base_price * days : 0;
	                const baseTotal = roomCost + packageCost;
	                const discount = Math.round(baseTotal * (specialDiscountPercent / 100));
	                const total = Math.max(0, baseTotal - discount);
	                const reservationFee = Math.round(total * (reservationFeePercent / 100));

                if (estimateTotal) {
                    estimateTotal.textContent = room || servicePackage ? money.format(total) : '—';
                }

                if (estimateHours) {
                    estimateHours.textContent = hoursPerDay
                        ? (days > 1 ? `${formatHours(hoursPerDay)} × ${days} days` : formatHours(hoursPerDay))
                        : '—';
                }

                if (estimateRoom) {
                    estimateRoom.textContent = room
                        ? `Room: ${room.name} · ${money.format(room.hourly_rate)}/hr × ${formatHours(hoursPerDay)}${days > 1 ? ` × ${days} days` : ''} = ${money.format(roomCost)}`
                        : 'Choose a room to calculate the room cost.';
                }

	                if (estimatePackage) {
	                    estimatePackage.textContent = servicePackage
	                        ? `Package: ${servicePackage.name} · ${money.format(servicePackage.base_price)}${days > 1 ? ` × ${days} days = ${money.format(packageCost)}` : ''}`
	                        : 'Package: None';
	                }

	                if (estimateDiscount) {
	                    estimateDiscount.textContent = room || servicePackage
	                        ? `Discount: ${discount ? `-${money.format(discount)} · ${specialDiscountPercent}%` : 'None'}`
	                        : 'Discount: —';
	                }

	                if (estimateReservation) {
	                    estimateReservation.textContent = room || servicePackage
	                        ? `Reservation fee: ${money.format(reservationFee)} · ${reservationFeePercent}%`
                        : 'Reservation fee: —';
                }
            };

            const setTurnstileStatus = (message, isError = false) => {
                if (!turnstileStatus) {
                    return;
                }

                turnstileStatus.textContent = message;
                turnstileStatus.classList.remove('hidden', 'text-red-600', 'text-brand-navy/55');
                turnstileStatus.classList.add(isError ? 'text-red-600' : 'text-brand-navy/55');
            };

            const setWaitingForVerification = (waiting) => {
                if (!submitButton) {
                    return;
                }

                submitButton.disabled = waiting;
                submitButton.style.opacity = waiting ? '0.72' : '';
                submitButton.style.cursor = waiting ? 'wait' : '';
            };

            window.bookingTurnstileCallback = () => {
                const pending = form.dataset.turnstilePending === '1';
                form.dataset.turnstileVerified = '1';
                setWaitingForVerification(false);

                if (pending) {
                    setTurnstileStatus('Verification complete. Sending request...');
                    form.dataset.turnstilePending = '0';
                    form.requestSubmit(submitButton);
                } else {
                    turnstileStatus?.classList.add('hidden');
                }
            };

            window.bookingTurnstileExpiredCallback = () => {
                form.dataset.turnstileVerified = '0';
                form.dataset.turnstilePending = '0';
                setWaitingForVerification(false);
                setTurnstileStatus('Verification expired. Please send the request again.', true);
            };

            window.bookingTurnstileErrorCallback = () => {
                form.dataset.turnstileVerified = '0';
                form.dataset.turnstilePending = '0';
                setWaitingForVerification(false);
                setTurnstileStatus('Verification could not start. Please reload and try again.', true);
            };

            const matchingSlots = () => existingSlots.filter((slot) =>
                String(slot.classroom_id) === String(selectedRoom()) &&
                rangeDates().includes(slot.booking_date)
            );

            const exactMatchExists = () => matchingSlots().some((slot) => slot.time_block === selectedTime());
            const differentTimeExists = () => matchingSlots().some((slot) => slot.time_block !== selectedTime());

            const refreshWarning = () => {
                confirmation.value = '0';
                form.dataset.scheduleConfirmed = '0';
                const endDateInput = document.getElementById('booking_end_date');

                if (endDateInput && selectedDate()) {
                    endDateInput.min = selectedDate();
                }

                warning.classList.toggle('hidden', !differentTimeExists() || exactMatchExists());
            };

            form.addEventListener('change', (event) => {
                if (event.target.matches('#booking_date, #booking_end_date, #time_block')) {
                    refreshWarning();
                }

                refreshEstimate();
            });
            document.querySelectorAll('input[name="classroom_id"]').forEach((input) => {
                input.addEventListener('change', () => {
                    refreshWarning();
                    refreshEstimate();
                });
            });
            form.addEventListener('submit', (event) => {
                if (exactMatchExists()) {
                    event.preventDefault();
                    alert('This exact booking request already exists on one of the selected dates.');
                    return;
                }

                if (differentTimeExists() && confirmation.value !== '1') {
                    event.preventDefault();

                    if (confirm('You already have an active booking for this room on at least one selected date. Are you sure you want to request another time slot?')) {
                        confirmation.value = '1';
                        form.requestSubmit();
                    }

                    return;
                }

                if (form.dataset.scheduleConfirmed !== '1') {
                    event.preventDefault();

                    const days = bookingDayCount();
                    const message = days > 1
                        ? `You are requesting ${days} daily bookings from ${scheduleLabel()} at ${selectedTimeLabel()}. Staff will review each date before final confirmation. Continue?`
                        : `You are requesting ${scheduleLabel()} at ${selectedTimeLabel()}. Staff will review before final confirmation. Continue?`;

                    if (confirm(message)) {
                        form.dataset.scheduleConfirmed = '1';
                        form.requestSubmit();
                    }

                    return;
                }

                if (turnstileEnabled && form.dataset.turnstileVerified !== '1' && !turnstileResponse()) {
                    event.preventDefault();

                    if (!turnstileWidget || !window.turnstile || typeof window.turnstile.execute !== 'function') {
                        setTurnstileStatus('Verification is still loading. Please try again in a moment.', true);
                        return;
                    }

                    form.dataset.turnstilePending = '1';
                    setWaitingForVerification(true);
                    setTurnstileStatus('Starting verification...');
                    window.turnstile.execute(turnstileSelector);
                    return;
                }
            });

            refreshWarning();
            refreshEstimate();
        })();
    </script>
</x-app-layout>
