<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RyanChandler\LaravelCloudflareTurnstile\Contracts\ClientInterface;
use RyanChandler\LaravelCloudflareTurnstile\Responses\SiteverifyResponse;

class CloudflareTurnstileClient implements ClientInterface
{
    public function siteverify(string $response): SiteverifyResponse
    {
        try {
            $result = Http::asForm()
                ->acceptJson()
                ->connectTimeout(3)
                ->timeout(8)
                ->post((string) config('services.turnstile.verify_url'), [
                    'secret' => config('services.turnstile.secret'),
                    'response' => $response,
                ]);
        } catch (ConnectionException $exception) {
            Log::warning('Turnstile verification unavailable.', [
                'error' => $exception->getMessage(),
            ]);

            return (bool) config('services.turnstile.fail_open', false)
                ? SiteverifyResponse::success()
                : SiteverifyResponse::failure(['internal-error']);
        }

        if (! $result->ok()) {
            return (bool) config('services.turnstile.fail_open', false)
                ? SiteverifyResponse::success()
                : SiteverifyResponse::failure(['internal-error']);
        }

        return $result->json('success')
            ? SiteverifyResponse::success()
            : SiteverifyResponse::failure($result->json('error-codes', []));
    }

    public function dummy(): string
    {
        return self::RESPONSE_DUMMY_TOKEN;
    }
}
