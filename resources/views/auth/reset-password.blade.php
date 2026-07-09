<x-guest-layout>
    <x-authentication-card>
        <div class="text-center mb-6">
            <p class="eyebrow mb-2">{{ __("Set new password") }}</p>
            <h1 class="font-serif text-3xl text-brand-navy">{{ __("Choose a new password") }}</h1>
        </div>

        <x-validation-errors class="mb-4 text-red-600" />

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus />
            </div>
            <div>
                <x-label for="password" value="{{ __('New password') }}" />
                <x-input id="password" type="password" name="password" required autocomplete="new-password" />
            </div>
            <div>
                <x-label for="password_confirmation" value="{{ __('Confirm password') }}" />
                <x-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            <div class="pt-4">
                <x-button class="w-full">{{ __('Reset password') }}</x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
