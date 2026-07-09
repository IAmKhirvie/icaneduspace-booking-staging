<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SystemStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_health_endpoint_reports_database_status(): void
    {
        $this->get('/health')
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'database' => 'ok',
            ]);
    }

    public function test_staff_can_view_system_status_page(): void
    {
        Role::create(['name' => 'staff']);

        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $this->actingAs($staff)
            ->get(route('manage.status'))
            ->assertOk()
            ->assertSee('System status')
            ->assertSee('sqlite')
            ->assertSee('Mail');
    }

    public function test_guest_cannot_view_system_status_page(): void
    {
        $this->get(route('manage.status'))
            ->assertRedirect(route('staff.login'));
    }
}
