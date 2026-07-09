<x-app-layout>
    <x-slot name="header">
        <p class="eyebrow mb-1">{{ __('Account') }}</p>
        <h1 class="font-serif text-4xl text-brand-navy">{{ __('Settings') }}</h1>
        <p class="text-brand-navy/60 mt-2 max-w-2xl">{{ __('Manage your profile, password, sessions, and account activity.') }}</p>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @php
                $accountActivity = $accountActivity ?? collect();
                $accountSessions = $accountSessions ?? collect();
            @endphp

            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')

                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.update-password-form')
                </div>

                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.two-factor-authentication-form')
                </div>

                <x-section-border />
            @endif

            <div class="mt-10 sm:mt-0">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>

            <x-section-border />

            <div class="mt-10 sm:mt-0">
                <x-action-section>
                    <x-slot name="title">
                        {{ __('Session Log') }}
                    </x-slot>

                    <x-slot name="description">
                        {{ __('Recent active sessions for your account.') }}
                    </x-slot>

                    <x-slot name="content">
                        @if ($accountSessions->isNotEmpty())
                            <div class="divide-y divide-brand-navy/10">
                                @foreach ($accountSessions as $session)
                                    <div class="py-4 first:pt-0 last:pb-0 flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-brand-navy">
                                                {{ $session->agent->platform() ?: __('Unknown platform') }} · {{ $session->agent->browser() ?: __('Unknown browser') }}
                                            </p>
                                            <p class="text-xs text-brand-navy/55 mt-1">
                                                {{ $session->ip_address ?: __('Unknown IP') }} · {{ __('Last active') }} {{ $session->last_active_at->format('M d, Y H:i') }}
                                            </p>
                                        </div>
                                        @if ($session->is_current_device)
                                            <span class="status-badge status-approved">{{ __('This device') }}</span>
                                        @else
                                            <span class="text-xs uppercase tracking-[0.18em] text-brand-navy/50">{{ $session->last_active }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-brand-navy/60">{{ __('No active session records are available.') }}</p>
                        @endif
                    </x-slot>
                </x-action-section>
            </div>

            <x-section-border />

            <div class="mt-10 sm:mt-0">
                <x-action-section>
                    <x-slot name="title">
                        {{ __('Audit Log') }}
                    </x-slot>

                    <x-slot name="description">
                        {{ __('Recent account and booking activity connected to your login.') }}
                    </x-slot>

                    <x-slot name="content">
                        @if ($accountActivity->isNotEmpty())
                            <div class="divide-y divide-brand-navy/10">
                                @foreach ($accountActivity as $event)
                                    <div class="py-4 first:pt-0 last:pb-0">
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <p class="text-sm font-semibold text-brand-navy">
                                                {{ __(ucfirst($event->description)) }}
                                                @if ($event->subject_type)
                                                    <span class="font-normal text-brand-navy/50">
                                                        · {{ class_basename($event->subject_type) }} #{{ $event->subject_id }}
                                                    </span>
                                                @endif
                                            </p>
                                            <span class="text-xs uppercase tracking-[0.18em] text-brand-navy/45">
                                                {{ optional($event->created_at)->format('M d, Y H:i') }}
                                            </span>
                                        </div>
                                        @if ($event->log_name)
                                            <p class="text-xs text-brand-navy/55 mt-1">{{ __('Log') }}: {{ $event->log_name }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-brand-navy/60">{{ __('No account activity has been recorded yet.') }}</p>
                        @endif
                    </x-slot>
                </x-action-section>
            </div>

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <x-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire('profile.delete-user-form')
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
