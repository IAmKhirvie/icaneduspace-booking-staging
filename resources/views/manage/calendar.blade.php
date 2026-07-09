<x-app-layout>
    <x-slot name="header">
        <p class="eyebrow mb-1">{{ __("Staff") }}</p>
        <h1 class="font-serif text-4xl text-brand-navy">{{ __("Calendar") }}</h1>
        <p class="text-brand-navy/60 mt-1">{{ __("Pending bookings in gold, booked sessions in green. Click a slot to filter bookings.") }}</p>
    </x-slot>

    <div class="max-w-7xl mx-auto px-6 py-10">
        <div class="card p-5">
            <div id="bookings-calendar" style="min-height: calc(100vh - 320px);"></div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('bookings-calendar');
            const cal = new FullCalendar.Calendar(el, {
                initialView: 'timeGridWeek',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                slotMinTime: '08:00:00',
                slotMaxTime: '22:00:00',
                nowIndicator: true,
                height: 'auto',
                events: function (info, success, failure) {
                    fetch('{{ route("manage.calendar.events") }}?start=' + encodeURIComponent(info.startStr) + '&end=' + encodeURIComponent(info.endStr))
                        .then(r => r.json())
                        .then(success)
                        .catch(failure);
                },
                eventClick: function (info) {
                    if (info.event.url) {
                        info.jsEvent.preventDefault();
                        window.location.href = info.event.url;
                    }
                }
            });
            cal.render();
        });
    </script>
    <style>
        #bookings-calendar .fc-button-primary { background:#0D1C4C !important; border-color:#0D1C4C !important; color:#fff !important; text-transform:uppercase; font-size:.7rem; letter-spacing:.1em; }
        #bookings-calendar .fc-button-primary.fc-button-active { background:#D9A72F !important; border-color:#D9A72F !important; color:#07112f !important; }
        #bookings-calendar .fc-toolbar-title { font-family:'Cormorant Garamond', serif; color:#0D1C4C; }
        #bookings-calendar .fc-col-header-cell-cushion { color:#0D1C4C !important; text-decoration:none; }
        #bookings-calendar .fc-day-today { background:rgba(217,167,47,0.05) !important; }
    </style>
</x-app-layout>
