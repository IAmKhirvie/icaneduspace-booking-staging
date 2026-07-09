<x-guest-layout>
    <x-authentication-card>
        <div class="text-center mb-6">
            <p class="eyebrow mb-2">{{ __("Create account") }}</p>
            <h1 class="font-serif text-3xl text-brand-navy">{{ __("Join ICAN Eduspace") }}</h1>
        </div>

        <x-validation-errors class="mb-4 text-red-600" />

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            </div>

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
            </div>

            <div class="space-y-4" x-data="{ showPassword: false, showPasswordConfirmation: false }">
                <div>
                    <x-label for="password" value="{{ __('Password') }}" />
                    <div class="password-field relative">
                        <x-input id="password" x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="new-password" />
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

                <div>
                    <x-label for="password_confirmation" value="{{ __('Confirm password') }}" />
                    <div class="password-field relative">
                        <x-input id="password_confirmation" x-bind:type="showPasswordConfirmation ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" />
                        <button
                            type="button"
                            class="password-toggle-button absolute inset-y-0 right-0 flex items-center justify-center"
                            x-bind:aria-label="showPasswordConfirmation ? '{{ __('Hide password confirmation') }}' : '{{ __('Show password confirmation') }}'"
                            x-bind:title="showPasswordConfirmation ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
                            @click="showPasswordConfirmation = ! showPasswordConfirmation"
                        >
                            <svg x-show="! showPasswordConfirmation" xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg x-show="showPasswordConfirmation" x-cloak xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 9.88A3 3 0 0 0 14.12 14.12M6.1 6.6C3.63 8.2 2.25 12 2.25 12s3.75 6.75 9.75 6.75c1.7 0 3.25-.54 4.58-1.32M19.16 15.66C20.82 14.08 21.75 12 21.75 12s-3.75-6.75-9.75-6.75c-.9 0-1.76.15-2.56.41" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <label for="terms" class="flex items-start gap-2 pt-2 normal-case tracking-normal text-sm text-brand-navy/70">
                    <input id="terms" name="terms" type="checkbox" required class="!w-4 !h-4 mt-1 accent-brand-gold">
                    <span>
                        {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="link-gold underline">'.__('Terms of Service').'</a>',
                                'privacy_policy'   => '<a target="_blank" href="'.route('policy.show').'" class="link-gold underline">'.__('Privacy Policy').'</a>',
                        ]) !!}
                    </span>
                </label>
            @endif

            <div class="flex flex-col gap-4 pt-4">
                <x-button class="w-full">{{ __('Create account') }}</x-button>
                <p class="text-center text-xs uppercase tracking-[0.25em] text-brand-navy/50">
                    Already registered? <a class="link-gold" href="{{ route('login') }}">{{ __("Sign in") }}</a>
                </p>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
