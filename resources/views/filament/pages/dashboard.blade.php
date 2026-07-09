<x-filament-panels::page>
    <div class="staff-dashboard">
        <section class="staff-hero">
            <div>
                <p class="staff-eyebrow">Welcome</p>
                <h1>Hello, {{ auth()->user()->name }}.</h1>
                <p>Bookings, rooms, approvals, and shortcuts.</p>
            </div>
            <a href="{{ $links['createBooking'] }}" class="staff-btn-gold">+ New booking</a>
        </section>

        <section class="staff-stat-grid">
            <a href="{{ $links['bookings'] }}" class="staff-card staff-stat">
                <p class="staff-eyebrow">Pending</p>
                <strong>{{ $counts['pending'] }}</strong>
            </a>
            <a href="{{ $links['bookings'] }}" class="staff-card staff-stat">
                <p class="staff-eyebrow">Booked</p>
                <strong class="is-success">{{ $counts['approved'] }}</strong>
            </a>
            <a href="{{ $links['bookings'] }}" class="staff-card staff-stat">
                <p class="staff-eyebrow">Today</p>
                <strong>{{ $counts['today'] }}</strong>
            </a>
            <a href="{{ $links['bookings'] }}" class="staff-card staff-stat">
                <p class="staff-eyebrow">Past</p>
                <strong class="is-muted">{{ $counts['past'] }}</strong>
            </a>
        </section>

        <section>
            <div class="staff-section-head">
                <div>
                    <p class="staff-eyebrow">Upcoming</p>
                    <h2>Next sessions</h2>
                </div>
                <a href="{{ $links['bookings'] }}" class="staff-link">View all -></a>
            </div>

            <div class="staff-stack">
                @forelse($upcoming as $booking)
                    <a href="{{ \App\Filament\Resources\Bookings\BookingResource::getUrl('edit', ['record' => $booking]) }}" class="staff-card staff-booking-row">
                        <div class="staff-booking-main">
                            @if($booking->classroom?->hero_image)
                                <div class="staff-booking-image" style="background-image:url('{{ $booking->classroom->hero_image }}')"></div>
                            @endif
                            <div>
                                <p class="staff-eyebrow">#{{ $booking->id }} · {{ optional($booking->starts_at)->format('M d, Y') }}</p>
                                <h3>{{ $booking->purpose }}</h3>
                                <p class="staff-muted">
                                    {{ $booking->classroom?->name ?? 'No room assigned' }} ·
                                    {{ optional($booking->starts_at)->format('H:i') }}-{{ optional($booking->ends_at)->format('H:i') }}
                                    @if($booking->customer_name)
                                        · {{ $booking->customer_name }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <span class="staff-status staff-status-{{ $booking->status }}">{{ $booking->status }}</span>
                    </a>
                @empty
                    <div class="staff-card staff-empty">
                        <p>No upcoming sessions yet.</p>
                        <a href="{{ $links['createBooking'] }}" class="staff-btn-gold">Create booking</a>
                    </div>
                @endforelse
            </div>
        </section>

        <section>
            <div class="staff-section-head">
                <div>
                    <p class="staff-eyebrow">Rooms</p>
                    <h2>Browse spaces</h2>
                </div>
                <a href="{{ $links['rooms'] }}" class="staff-link">All rooms -></a>
            </div>

            <div class="staff-room-grid">
                @foreach($rooms as $room)
                    <a href="{{ \App\Filament\Resources\Classrooms\ClassroomResource::getUrl('edit', ['record' => $room]) }}" class="staff-room-card">
                        <div class="staff-room-image" style="background-image:url('{{ $room->hero_image }}')"></div>
                        <div class="staff-room-scrim"></div>
                        <div class="staff-room-content">
                            <p class="staff-eyebrow">{{ $room->location }}</p>
                            <h3>{{ $room->name }}</h3>
                            <p>{{ $room->capacity }} seats · {{ \App\Support\Money::format($room->hourly_rate) }}/hr</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        <section>
            <div class="staff-section-head">
                <div>
                    <p class="staff-eyebrow">Tools</p>
                    <h2>Staff shortcuts</h2>
                </div>
            </div>
            <div class="staff-shortcuts">
                <a href="{{ $links['bookings'] }}" class="staff-card staff-shortcut">
                    <span>Bookings</span>
                    <small>Review requests and update statuses.</small>
                </a>
                <a href="{{ $links['rooms'] }}" class="staff-card staff-shortcut">
                    <span>Rooms</span>
                    <small>Manage spaces, capacity, rates, and images.</small>
                </a>
                <a href="{{ $links['packages'] }}" class="staff-card staff-shortcut">
                    <span>Packages</span>
                    <small>Maintain public service packages.</small>
                </a>
            </div>
        </section>
    </div>
</x-filament-panels::page>
