<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TurnstileVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Show password');
    }

    public function test_admin_and_staff_login_screens_include_password_visibility_control(): void
    {
        $this->get('/admin/login')
            ->assertStatus(200)
            ->assertSee('Show password');

        $this->get('/staff/login')
            ->assertStatus(200)
            ->assertSee('Show password');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_turnstile_is_bypassed_for_configured_non_production_environments(): void
    {
        config([
            'services.turnstile.enabled' => true,
            'services.turnstile.bypass_environments' => ['testing'],
        ]);

        $this->assertFalse(app(TurnstileVerifier::class)->enabled());

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee('challenges.cloudflare.com');
        $response->assertDontSee('data-sitekey');
    }

    public function test_login_requires_turnstile_when_enabled_without_environment_bypass(): void
    {
        config([
            'services.turnstile.enabled' => true,
            'services.turnstile.bypass_environments' => [],
            'services.turnstile.secret' => 'test-secret',
        ]);

        $user = User::factory()->create();

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'cf-turnstile-response' => 'Please complete the human verification check.',
        ]);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }
}
