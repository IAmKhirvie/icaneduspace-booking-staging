<x-app-layout>
    <x-slot name="header">
        <p class="eyebrow mb-1">{{ $user->exists ? __('Edit') : __('New') }}</p>
        <h1 class="font-serif text-4xl text-brand-navy">{{ $user->exists ? $user->name : __('New user') }}</h1>
    </x-slot>

    <div class="max-w-3xl mx-auto px-6 py-10 space-y-6">

        @if ($errors->any())
            <div class="border border-red-300 bg-red-50 px-5 py-3 text-sm text-red-700">
                <ul class="space-y-1">@foreach($errors->all() as $e)<li>· {{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ $user->exists ? route('manage.users.update', $user) : route('manage.users.store') }}"
              class="card p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
            @csrf
            <input type="hidden" name="return" value="{{ $returnUrl }}">
            <div>
                <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __('Name') }}</label>
                <input name="name" required value="{{ old('name', $user->name) }}">
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __('Email') }}</label>
                <input name="email" type="email" required value="{{ old('email', $user->email) }}">
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ $user->exists ? __('New password (leave blank to keep)') : __('Password') }}</label>
                <input name="password" type="password" {{ $user->exists ? '' : 'required' }} autocomplete="new-password">
            </div>
            <div>
                <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __('Confirm password') }}</label>
                <input name="password_confirmation" type="password" autocomplete="new-password">
            </div>

            <div class="md:col-span-2">
                <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-2 block">{{ __('Roles') }}</label>
                <div class="flex flex-wrap gap-3">
                    @foreach($allRoles as $role)
                        @php $checked = $user->exists ? $user->roles->pluck('name')->contains($role) : ($role === 'customer'); @endphp
                        <label class="flex items-center gap-2 text-sm normal-case tracking-normal text-brand-navy/75 border border-brand-navy/15 px-3 py-2">
                            <input type="checkbox" name="roles[]" value="{{ $role }}" @checked(old('roles', $checked ? [$role] : []) && in_array($role, old('roles', $checked ? [$role] : []))) class="!w-4 !h-4 accent-brand-gold">
                            <span>{{ $role }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-brand-navy/45 mt-2">{{ __('Tip: assign super_admin or admin for full access, staff for booking management, customer for booking only.') }}</p>
            </div>

            <div class="md:col-span-2 flex gap-3 justify-between pt-3">
                <a href="{{ $returnUrl }}" class="btn-ghost">← {{ __('Cancel') }}</a>
                <button class="btn-gold">{{ $user->exists ? __('Save changes') : __('Create user') }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
