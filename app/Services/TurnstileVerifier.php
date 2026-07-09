<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RyanChandler\LaravelCloudflareTurnstile\Rules\Turnstile;

class TurnstileVerifier
{
    public const RESPONSE_FIELD = 'cf-turnstile-response';

    private const REQUEST_VALIDATED_ATTRIBUTE = 'turnstile.validated';

    public function validate(Request $request, string $field = self::RESPONSE_FIELD): void
    {
        if (! $this->enabled()) {
            return;
        }

        if ($request->attributes->get(self::REQUEST_VALIDATED_ATTRIBUTE) === true) {
            return;
        }

        $secret = (string) config('services.turnstile.secret', '');

        if ($secret === '') {
            throw ValidationException::withMessages([
                $field => 'Human verification is not configured.',
            ]);
        }

        Validator::make(
            $request->all(),
            [$field => ['required', new Turnstile]],
            [$field.'.required' => 'Please complete the human verification check.'],
        )->validate();

        $request->attributes->set(self::REQUEST_VALIDATED_ATTRIBUTE, true);
    }

    public function enabled(): bool
    {
        if (! (bool) config('services.turnstile.enabled', false)) {
            return false;
        }

        $bypassEnvironments = config('services.turnstile.bypass_environments', []);

        if (! is_array($bypassEnvironments)) {
            $bypassEnvironments = [];
        }

        return ! app()->environment($bypassEnvironments);
    }
}
