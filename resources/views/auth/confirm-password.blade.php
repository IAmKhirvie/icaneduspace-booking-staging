<x-guest-layout>
    <x-authentication-card>
        <div class="text-center mb-6">
            <p class="eyebrow mb-2">{{ __("Secure area") }}</p>
            <h1 class="font-serif text-3xl text-brand-navy">{{ __("Confirm password") }}</h1>
            <p class="mt-3 text-sm text-brand-navy/65 normal-case tracking-normal">
                Confirm your password before continuing.
            </p>
        </div>

        <x-validation-errors class="mb-4 text-red-600" />

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
            @csrf
            <div>
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" type="password" name="password" required autocomplete="current-password" autofocus />
            </div>
            <div class="pt-4">
                <x-button class="w-full">{{ __('Confirm') }}</x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
