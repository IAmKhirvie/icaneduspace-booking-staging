<x-guest-layout>
    <x-authentication-card>
        <div class="text-center mb-6">
            @php
                $portal = $portal ?? request('portal');
                $isStaffPortal = in_array($portal, ['admin', 'staff'], true)
                    || request()->is('admin/login', 'staff/login')
                    || request()->boolean('staff')
                    || request()->boolean('admin');
            @endphp
            <p class="eyebrow mb-2">{{ $isStaffPortal ? __('Staff access') : __('Welcome back') }}</p>
            <h1 class="font-serif text-3xl text-brand-navy">{{ $isStaffPortal ? __('Admin / Staff sign in') : __('Sign in') }}</h1>
        </div>

        <x-validation-errors class="mb-4 text-red-600" />

        @session('status')
            <div class="mb-4 border border-emerald-400 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}" class="space-y-4" data-turnstile-form>
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div x-data="{ showPassword: false }">
                <x-label for="password" value="{{ __('Password') }}" />
                <div class="password-field relative">
                    <x-input id="password" x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" />
                    <button
                        type="button"
                        class="password-toggle-button absolute inset-y-0 right-0 flex items-center justify-center"
                        x-bind:aria-label="showPassword ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
                        x-bind:title="showPassword ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
                        @click="showPassword = ! showPassword"
                    >
                        <svg x-show="! showPassword" xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg x-show="showPassword" x-cloak xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 9.88A3 3 0 0 0 14.12 14.12M6.1 6.6C3.63 8.2 2.25 12 2.25 12s3.75 6.75 9.75 6.75c1.7 0 3.25-.54 4.58-1.32M19.16 15.66C20.82 14.08 21.75 12 21.75 12s-3.75-6.75-9.75-6.75c-.9 0-1.76.15-2.56.41" />
                        </svg>
                    </button>
                </div>
            </div>

            <label for="remember_me" class="flex items-center pt-2 normal-case tracking-normal text-sm text-brand-navy/70">
                <input id="remember_me" name="remember" type="checkbox"
                       value="1"
                       class="!w-4 !h-4 !p-0 mr-2 rounded border-brand-navy/40 text-brand-gold accent-brand-gold focus:ring-brand-gold focus:ring-offset-0">
                <span>{{ __('Keep me signed in') }}</span>
            </label>

            @if (app(\App\Services\TurnstileVerifier::class)->enabled())
                <div class="flex justify-center">
                    <x-turnstile data-appearance="always" />
                </div>
                @error('cf-turnstile-response')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            @endif

            <div class="flex flex-col gap-4 pt-4">
                <x-button class="w-full" data-submit-button>{{ __('Log in') }}</x-button>

                @if (Route::has('password.request'))
                    <a class="text-xs uppercase tracking-[0.25em] text-center" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                @if (Route::has('register'))
                    <p class="text-center text-xs uppercase tracking-[0.25em] text-brand-navy/50">
                        New here? <a class="link-gold" href="{{ route('register') }}">{{ __("Create an account") }}</a>
                    </p>
                @endif
            </div>
        </form>
    </x-authentication-card>

    <script>
        document.querySelectorAll('[data-turnstile-form]').forEach((form) => {
            form.addEventListener('submit', () => {
                const button = form.querySelector('[data-submit-button]');

                if (button) {
                    button.disabled = true;
                    button.style.opacity = '0.72';
                    button.style.cursor = 'wait';
                }
            });
        });
    </script>
</x-guest-layout>
