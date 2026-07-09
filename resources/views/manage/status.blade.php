<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-end gap-4 flex-wrap">
            <div>
                <p class="eyebrow mb-1">{{ __("Operations") }}</p>
                <h1 class="font-serif text-4xl text-brand-navy">{{ __("System status") }}</h1>
                <p class="text-brand-navy/60 mt-1">{{ __("Generated") }} {{ $status['generated_at'] }}</p>
            </div>
            <span class="status-badge {{ $status['overall'] === 'ok' ? 'status-approved' : 'status-pending' }}">
                {{ $status['overall'] }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="card p-6">
                <p class="eyebrow mb-2">{{ __("App") }}</p>
                <h2 class="font-serif text-2xl text-brand-navy">{{ $status['app']['environment'] }}</h2>
                <p class="text-sm text-brand-navy/60 mt-2">{{ $status['app']['url'] }}</p>
                <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/45 mt-4">{{ __("Debug") }}: {{ $status['app']['debug'] ? 'on' : 'off' }}</p>
            </div>

            <div class="card p-6">
                <p class="eyebrow mb-2">{{ __("Database") }}</p>
                <h2 class="font-serif text-2xl text-brand-navy">{{ $status['database']['connection'] }}</h2>
                <p class="text-sm {{ $status['database']['ok'] ? 'text-emerald-700' : 'text-red-700' }} mt-2">{{ $status['database']['message'] }}</p>
                <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/45 mt-4">{{ __("Size") }}: {{ $status['database']['size_label'] ?? 'n/a' }}</p>
            </div>

            <div class="card p-6">
                <p class="eyebrow mb-2">{{ __("Mail") }}</p>
                <h2 class="font-serif text-2xl text-brand-navy">{{ $status['mail']['mailer'] }}</h2>
                <p class="text-sm {{ $status['mail']['ok'] ? 'text-emerald-700' : 'text-amber-700' }} mt-2">{{ $status['mail']['message'] }}</p>
                <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/45 mt-4">{{ $status['mail']['from'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="eyebrow mb-2">{{ __("Backup") }}</p>
                        <h2 class="font-serif text-2xl text-brand-navy">
                            {{ $status['backup']['latest']['snapshot_id'] ?? __('No snapshot') }}
                        </h2>
                    </div>
                    <span class="status-badge {{ $status['backup']['ok'] ? 'status-approved' : 'status-pending' }}">{{ $status['backup']['ok'] ? 'ok' : 'attention' }}</span>
                </div>
                <p class="text-sm text-brand-navy/65 mt-4">{{ $status['backup']['message'] }}</p>
                @if($status['backup']['latest'])
                    <dl class="mt-5 grid grid-cols-1 gap-2 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-brand-navy/55">{{ __("Created") }}</dt><dd class="text-brand-navy text-right">{{ $status['backup']['latest']['created_at'] }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-brand-navy/55">{{ __("Health") }}</dt><dd class="text-brand-navy text-right">{{ $status['backup']['latest']['health_status'] ?? 'n/a' }}</dd></div>
                    </dl>
                @endif
            </div>

            <div class="card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="eyebrow mb-2">{{ __("Scheduler") }}</p>
                        <h2 class="font-serif text-2xl text-brand-navy">{{ $status['scheduler']['last_run_at'] ?? __('No run') }}</h2>
                    </div>
                    <span class="status-badge {{ $status['scheduler']['ok'] ? 'status-approved' : 'status-pending' }}">{{ $status['scheduler']['ok'] ? 'ok' : 'attention' }}</span>
                </div>
                <p class="text-sm text-brand-navy/65 mt-4">{{ $status['scheduler']['message'] }}</p>
                <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/45 mt-4">{{ __("Age minutes") }}: {{ $status['scheduler']['age_minutes'] ?? 'n/a' }}</p>
            </div>
        </div>

        <div class="card overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-brand-navy/5">
                    <tr class="text-left text-xs uppercase tracking-[0.12em] text-brand-navy/65">
                        <th class="px-4 py-3">{{ __("Metric") }}</th>
                        <th class="px-4 py-3 text-right">{{ __("Count") }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($status['counts'] as $label => $count)
                        <tr class="border-t border-brand-navy/10">
                            <td class="px-4 py-3 text-brand-navy/70">{{ str_replace('_', ' ', $label) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-brand-navy">{{ $count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
