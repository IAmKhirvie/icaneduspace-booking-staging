<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\ServicePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_cors_rejects_unapproved_origins(): void
    {
        config(['cors.allowed_origins' => ['https://booking.icanacademy.work']]);

        $response = $this
            ->withHeaders([
                'Origin' => 'https://evil.example',
                'Access-Control-Request-Method' => 'POST',
            ])
            ->options('/api/bookings');

        $response->assertForbidden();
        $this->assertNull($response->headers->get('Access-Control-Allow-Origin'));
    }

    public function test_cors_allows_approved_origins(): void
    {
        config(['cors.allowed_origins' => ['https://booking.icanacademy.work']]);

        $response = $this
            ->withHeaders([
                'Origin' => 'https://booking.icanacademy.work',
                'Access-Control-Request-Method' => 'POST',
            ])
            ->options('/api/bookings');

        $this->assertSame('https://booking.icanacademy.work', $response->headers->get('Access-Control-Allow-Origin'));
    }

    public function test_api_docs_are_not_public(): void
    {
        $this->get('/docs')
            ->assertRedirect(route('login'));
    }

    public function test_inactive_public_api_resources_are_hidden(): void
    {
        $classroom = Classroom::create([
            'name' => 'Hidden Room',
            'slug' => 'hidden-room',
            'capacity' => 12,
            'hourly_rate' => 800,
            'is_active' => false,
        ]);
        $package = ServicePackage::create([
            'name' => 'Hidden Package',
            'slug' => 'hidden-package',
            'base_price' => 1000,
            'is_active' => false,
        ]);

        $this->getJson('/api/classrooms/'.$classroom->id)->assertNotFound();
        $this->getJson('/api/service-packages/'.$package->id)->assertNotFound();
    }

    public function test_api_booking_status_filter_is_validated(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/bookings?status=invalid-status')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_user_management_uses_numbered_cursor_pagination_controls(): void
    {
        Role::create(['name' => 'admin']);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        User::factory()->count(55)->create();

        $response = $this
            ->actingAs($admin)
            ->get(route('manage.users.index', ['per_page' => 20]));

        $response->assertOk();
        $response->assertSee('name="per_page"', false);
        $response->assertSee('<option value="10"', false);
        $response->assertSee('<option value="20" selected', false);
        $response->assertSee('<option value="25"', false);
        $response->assertSee('<option value="75"', false);
        $response->assertSee('name="role"', false);
        $response->assertSee('name="sort"', false);
        $response->assertSee('before=', false);
        $response->assertSee('data-restore-scroll', false);
        $response->assertSee('userManagementList()', false);
        $response->assertSee('aria-current="page">1', false);
        $response->assertDontSee('[1]');
        $response->assertSee('&lt;&lt;', false);
        $response->assertSee('&gt;&gt;', false);
    }
}
