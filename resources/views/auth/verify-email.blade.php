<x-guest-layout>
    <x-authentication-card>
        <div class="text-center mb-6">
            <p class="eyebrow mb-2">{{ __("One more step") }}</p>
            <h1 class="font-serif text-3xl text-brand-navy">{{ __("Verify your email") }}</h1>
            <p class="mt-3 text-sm text-brand-navy/65 normal-case tracking-normal">
                We sent a verification link to your email. Click it to activate your account.
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 border border-emerald-400 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ __('A new verification link has been sent.') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
            @csrf
            <x-button class="w-full">{{ __('Resend verification email') }}</x-button>
        </form>

        <div class="mt-6 flex justify-between text-xs uppercase tracking-[0.25em]">
            <a href="{{ route('profile.show') }}" class="link-gold">{{ __("Edit profile") }}</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="link-gold">{{ __("Log out") }}</button>
            </form>
        </div>
    </x-authentication-card>
</x-guest-layout>
