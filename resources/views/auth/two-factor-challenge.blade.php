<x-guest-layout>
    <x-authentication-card>
        <div x-data="{ recovery: false }">
            <div class="text-center mb-6">
                <p class="eyebrow mb-2">Two-factor verification</p>
                <h1 class="font-serif text-3xl text-brand-navy">{{ __("Confirm it's you") }}</h1>
                <p class="mt-3 text-sm text-brand-navy/65 normal-case tracking-normal" x-show="!recovery">
                    Enter the code from your authenticator app.
                </p>
                <p class="mt-3 text-sm text-brand-navy/65 normal-case tracking-normal" x-cloak x-show="recovery">
                    Enter one of your emergency recovery codes.
                </p>
            </div>

            <x-validation-errors class="mb-4 text-red-600" />

            <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-4">
                @csrf
                <div x-show="!recovery">
                    <x-label for="code" value="{{ __('Code') }}" />
                    <x-input id="code" type="text" inputmode="numeric" name="code" autofocus x-ref="code" autocomplete="one-time-code" />
                </div>
                <div x-cloak x-show="recovery">
                    <x-label for="recovery_code" value="{{ __('Recovery code') }}" />
                    <x-input id="recovery_code" type="text" name="recovery_code" x-ref="recovery_code" autocomplete="one-time-code" />
                </div>

                <div class="flex flex-col gap-4 pt-4">
                    <x-button class="w-full">{{ __('Verify') }}</x-button>
                    <button type="button"
                            x-show="!recovery"
                            x-on:click="recovery = true; $nextTick(() => $refs.recovery_code.focus())"
                            class="link-gold text-xs uppercase tracking-[0.25em] text-center">
                        Use a recovery code
                    </button>
                    <button type="button" x-cloak
                            x-show="recovery"
                            x-on:click="recovery = false; $nextTick(() => $refs.code.focus())"
                            class="link-gold text-xs uppercase tracking-[0.25em] text-center">
                        Use an authentication code
                    </button>
                </div>
            </form>
        </div>
    </x-authentication-card>
</x-guest-layout>
