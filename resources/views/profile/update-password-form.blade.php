<x-form-section submit="updatePassword">
    <x-slot name="title">
        {{ __('Update Password') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Ensure your account is using a long, random password to stay secure.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4 space-y-6" x-data="{ showCurrentPassword: false, showNewPassword: false, showConfirmPassword: false }">
            <div>
                <x-label for="current_password" value="{{ __('Current Password') }}" />
                <div class="password-field relative mt-1">
                    <x-input id="current_password" x-bind:type="showCurrentPassword ? 'text' : 'password'" class="block w-full" wire:model="state.current_password" autocomplete="current-password" />
                    <button
                        type="button"
                        class="password-toggle-button absolute inset-y-0 right-0 flex items-center justify-center"
                        x-bind:aria-label="showCurrentPassword ? '{{ __('Hide current password') }}' : '{{ __('Show current password') }}'"
                        x-bind:title="showCurrentPassword ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
                        @click="showCurrentPassword = ! showCurrentPassword"
                    >
                        <svg x-show="! showCurrentPassword" xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg x-show="showCurrentPassword" x-cloak xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 9.88A3 3 0 0 0 14.12 14.12M6.1 6.6C3.63 8.2 2.25 12 2.25 12s3.75 6.75 9.75 6.75c1.7 0 3.25-.54 4.58-1.32M19.16 15.66C20.82 14.08 21.75 12 21.75 12s-3.75-6.75-9.75-6.75c-.9 0-1.76.15-2.56.41" />
                        </svg>
                    </button>
                </div>
                <x-input-error for="current_password" class="mt-2" />
            </div>

            <div>
                <x-label for="password" value="{{ __('New Password') }}" />
                <div class="password-field relative mt-1">
                    <x-input id="password" x-bind:type="showNewPassword ? 'text' : 'password'" class="block w-full" wire:model="state.password" autocomplete="new-password" />
                    <button
                        type="button"
                        class="password-toggle-button absolute inset-y-0 right-0 flex items-center justify-center"
                        x-bind:aria-label="showNewPassword ? '{{ __('Hide new password') }}' : '{{ __('Show new password') }}'"
                        x-bind:title="showNewPassword ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
                        @click="showNewPassword = ! showNewPassword"
                    >
                        <svg x-show="! showNewPassword" xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg x-show="showNewPassword" x-cloak xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 9.88A3 3 0 0 0 14.12 14.12M6.1 6.6C3.63 8.2 2.25 12 2.25 12s3.75 6.75 9.75 6.75c1.7 0 3.25-.54 4.58-1.32M19.16 15.66C20.82 14.08 21.75 12 21.75 12s-3.75-6.75-9.75-6.75c-.9 0-1.76.15-2.56.41" />
                        </svg>
                    </button>
                </div>
                <x-input-error for="password" class="mt-2" />
            </div>

            <div>
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <div class="password-field relative mt-1">
                    <x-input id="password_confirmation" x-bind:type="showConfirmPassword ? 'text' : 'password'" class="block w-full" wire:model="state.password_confirmation" autocomplete="new-password" />
                    <button
                        type="button"
                        class="password-toggle-button absolute inset-y-0 right-0 flex items-center justify-center"
                        x-bind:aria-label="showConfirmPassword ? '{{ __('Hide password confirmation') }}' : '{{ __('Show password confirmation') }}'"
                        x-bind:title="showConfirmPassword ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
                        @click="showConfirmPassword = ! showConfirmPassword"
                    >
                        <svg x-show="! showConfirmPassword" xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg x-show="showConfirmPassword" x-cloak xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 9.88A3 3 0 0 0 14.12 14.12M6.1 6.6C3.63 8.2 2.25 12 2.25 12s3.75 6.75 9.75 6.75c1.7 0 3.25-.54 4.58-1.32M19.16 15.66C20.82 14.08 21.75 12 21.75 12s-3.75-6.75-9.75-6.75c-.9 0-1.76.15-2.56.41" />
                        </svg>
                    </button>
                </div>
                <x-input-error for="password_confirmation" class="mt-2" />
            </div>
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button>
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>
