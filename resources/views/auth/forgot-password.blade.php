<x-guest-layout>
    <x-authentication-card>
        <div class="text-center mb-6">
            <p class="eyebrow mb-2">{{ __("Account recovery") }}</p>
            <h1 class="font-serif text-3xl text-brand-navy">{{ __("Reset password") }}</h1>
            <p class="mt-3 text-sm text-brand-navy/65 normal-case tracking-normal">
                Enter your email and we'll send you a reset link.
            </p>
        </div>

        @session('status')
            <div class="mb-4 border border-emerald-400 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ $value }}</div>
        @endsession

        <x-validation-errors class="mb-4 text-red-600" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <div class="flex flex-col gap-4 pt-4">
                <x-button class="w-full">{{ __('Email reset link') }}</x-button>
                <a href="{{ route('login') }}" class="text-xs uppercase tracking-[0.25em] text-center">{{ __("Back to sign in") }}</a>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
