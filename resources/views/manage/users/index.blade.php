<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-end gap-4 flex-wrap">
            <div>
                <p class="eyebrow mb-1">{{ __('Admin') }}</p>
                <h1 class="font-serif text-4xl text-brand-navy">{{ __('User management') }}</h1>
                <p class="text-brand-navy/60 mt-1">{{ __('Add staff, change roles, or remove accounts.') }}</p>
            </div>
            <a href="{{ route('manage.users.create') }}" class="btn-gold">+ {{ __('New user') }}</a>
        </div>
    </x-slot>

    <div
        class="max-w-6xl mx-auto px-6 py-10 space-y-6"
        x-data="userManagementList()"
        x-init="restoreScroll()"
    >

        @if (session('flash'))
            <div class="border border-emerald-400 bg-emerald-50 px-5 py-3 text-sm text-emerald-700">{{ session('flash') }}</div>
        @endif

        <form method="GET" class="card p-5 space-y-4" @submit="loading = true">
            <div class="flex gap-3 items-end flex-wrap">
                <div class="flex-1 min-w-[200px]">
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __('Search') }}</label>
                    <input type="text" name="q" value="{{ $q }}" placeholder="{{ __('Name or email') }}">
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __('Display') }}</label>
                    <select name="per_page">
                        @foreach([10, 20, 25, 50, 75, 100] as $size)
                            <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn-gold">{{ __('Apply') }}</button>
                <a href="{{ route('manage.users.index') }}" class="btn-ghost" @click="loading = true">{{ __('Reset') }}</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 border-t border-brand-navy/10 pt-4">
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __('Role filter') }}</label>
                    <select name="role">
                        <option value="">{{ __('All roles') }}</option>
                        @foreach($allRoles as $roleName)
                            <option value="{{ $roleName }}" @selected($role === $roleName)>{{ $roleName }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __('Sort') }}</label>
                    <select name="sort">
                        <option value="newest" @selected($sort === 'newest')>{{ __('Newest first') }}</option>
                        <option value="oldest" @selected($sort === 'oldest')>{{ __('Oldest first') }}</option>
                    </select>
                </div>
            </div>
        </form>

        <div
            class="card overflow-hidden relative"
            :class="{ 'pointer-events-none': loading }"
            aria-live="polite"
        >
            <div x-show="loading" x-cloak class="absolute inset-0 z-10 bg-white/85 backdrop-blur-[1px]">
                <div class="pt-12">
                    @for($i = 0; $i < min($perPage, 8); $i++)
                        <div class="grid grid-cols-5 gap-4 px-4 py-4 border-t border-brand-navy/10 first:border-t-0">
                            <div class="h-4 bg-brand-navy/10 animate-pulse"></div>
                            <div class="h-4 bg-brand-navy/10 animate-pulse"></div>
                            <div class="h-4 bg-brand-navy/10 animate-pulse"></div>
                            <div class="h-4 bg-brand-navy/10 animate-pulse"></div>
                            <div class="h-4 bg-brand-navy/10 animate-pulse"></div>
                        </div>
                    @endfor
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-sm">
                    <thead class="bg-brand-navy/5">
                        <tr class="text-left text-xs uppercase tracking-[0.12em] text-brand-navy/65">
                            <th class="px-4 py-3">{{ __('ID') }}</th>
                            <th class="px-4 py-3">{{ __('Name') }}</th>
                            <th class="px-4 py-3">{{ __('Email') }}</th>
                            <th class="px-4 py-3">{{ __('Roles') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr class="border-t border-brand-navy/10 hover:bg-brand-navy/[0.02]">
                                <td class="px-4 py-3 text-brand-navy/55">#{{ $u->id }}</td>
                                <td class="px-4 py-3 font-medium text-brand-navy">{{ $u->name }}</td>
                                <td class="px-4 py-3 text-brand-navy/65">{{ $u->email }}</td>
                                <td class="px-4 py-3">
                                    @foreach($u->roles as $r)
                                        <span class="status-badge status-{{ in_array($r->name,['admin','super_admin'])?'approved':(in_array($r->name,['staff'])?'pending':'cancelled') }}">{{ $r->name }}</span>
                                    @endforeach
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex gap-2 items-center justify-end">
                                        <a
                                            href="{{ route('manage.users.edit', ['user' => $u, 'return' => $returnUrl]) }}"
                                            class="btn-ghost"
                                            data-restore-scroll
                                            @click="storeScroll(); loading = true"
                                        >{{ __('Edit') }}</a>
                                        @if($u->id !== auth()->id())
                                            <form method="POST" action="{{ route('manage.users.destroy', $u) }}" class="inline-flex" onsubmit="return confirm('Delete {{ $u->name }}?');">
                                                @csrf
                                                <input type="hidden" name="return" value="{{ $returnUrl }}">
                                                <button type="submit" class="btn-ghost" style="border-color:#b91c1c;color:#b91c1c;">{{ __('Delete') }}</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-brand-navy/55">{{ __('No users found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <p class="text-sm text-brand-navy/55">
                {{ __('Showing') }} {{ $pagination['from'] }}-{{ $pagination['to'] }} {{ __('of') }} {{ $pagination['total'] }}
            </p>

            <nav class="flex flex-wrap items-center gap-2 text-sm" aria-label="User management pages">
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
                    aria-label="First user page"
                >&lt;&lt;</a>
                <a
                    href="{{ $pagination['previous_url'] ?? '#' }}"
                    class="btn-ghost {{ $pagination['previous_url'] ? '' : 'pointer-events-none opacity-40' }}"
                    @click="storeScroll(); loading = true"
                    aria-label="Previous user page"
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
                    aria-label="Next user page"
                >&gt;</a>
                <a
                    href="{{ $pagination['last_url'] }}"
                    class="btn-ghost {{ $pagination['current_page'] === $pagination['last_page'] ? 'pointer-events-none opacity-40' : '' }}"
                    @click="storeScroll(); loading = true"
                    aria-label="Last user page"
                >&gt;&gt;</a>
            </nav>
        </div>
    </div>

    <script>
        function userManagementList() {
            return {
                loading: false,
                storageKey: 'ican:user-management-scroll:' + window.location.pathname + window.location.search,
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
            };
        }
    </script>
</x-app-layout>
