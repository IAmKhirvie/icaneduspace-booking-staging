<?php

namespace Tests\Feature;

use App\Models\BookingNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sidebar_packages_link_points_to_home_packages_section(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('data-sidebar-link="packages"', false);
        $response->assertSee('href="/#packages"', false);
    }

    public function test_sidebar_account_dropdown_exposes_settings_language_and_logout(): void
    {
        $user = User::factory()->create(['name' => 'ICAN Admin']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Signed in as');
        $response->assertSee('ICAN Admin');
        $response->assertSeeInOrder(['Settings', 'Signed in as', 'ICAN Admin']);
        $response->assertSee('href="'.route('settings').'"', false);
        $response->assertSee('Settings');
        $response->assertSee('EN');
        $response->assertSee('KO');
        $response->assertSee('action="'.route('logout').'"', false);
    }

    public function test_settings_page_renders_account_security_sections(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/settings');

        $response->assertStatus(200);
        $response->assertSee('Settings');
        $response->assertSee('Profile Information');
        $response->assertSee('Update Password');
        $response->assertSee('Show current password');
        $response->assertSee('Show new password');
        $response->assertSee('Show password confirmation');
        $response->assertSee('Browser Sessions');
        $response->assertSee('Session Log');
        $response->assertSee('Audit Log');
    }

    public function test_staff_topbar_exposes_search_notifications_and_calendar_tools(): void
    {
        Role::create(['name' => 'staff']);

        $staff = User::factory()->create();
        $staff->assignRole('staff');

        BookingNotification::create([
            'user_id' => $staff->id,
            'audience' => BookingNotification::AUDIENCE_STAFF,
            'channel' => 'mail',
            'notification_type' => 'App\\Notifications\\BookingSubmittedToStaff',
            'recipient' => 'staff',
            'subject' => 'Booking notice',
            'message' => 'Review a booking.',
            'status' => BookingNotification::STATUS_SENT,
        ]);

        $response = $this->actingAs($staff)->get('/staff/dashboard');

        $response->assertOk();
        $response->assertSee('data-app-topbar', false);
        $response->assertSee('.app-topbar { position:sticky; top:0; z-index:40;', false);
        $response->assertSee('data-topbar-tool="search"', false);
        $response->assertSee('action="'.route('search.index').'"', false);
        $response->assertSee('Search bookings, rooms, packages');
        $response->assertSee('topbarSearchSuggest', false);
        $response->assertSee('dashboard\/search\/suggestions', false);
        $response->assertSee('data-search-suggestions', false);
        $response->assertSee('data-search-correction', false);
        $response->assertSee('data-search-suggestion-section', false);
        $response->assertSee('data-search-suggestion-item', false);
        $response->assertSee('Full search');
        $response->assertSee('data-topbar-tool="notifications"', false);
        $response->assertSee('href="'.route('manage.notifications.index').'"', false);
        $response->assertSee('data-notification-toggle', false);
        $response->assertSee('data-notification-popover', false);
        $response->assertSee('data-notification-list', false);
        $response->assertSee('data-notification-read-all', false);
        $response->assertSee('data-notification-full-link', false);
        $response->assertSee('action="'.route('manage.notifications.read-all').'"', false);
        $response->assertSee('Booking notice');
        $response->assertSee('app-topbar-badge', false);
        $response->assertSee('data-topbar-tool="quick-action"', false);
        $response->assertSee('data-calendar-toggle', false);
        $response->assertSee('data-calendar-popover', false);
        $response->assertSee('data-calendar-month-grid', false);
        $response->assertSee('data-calendar-prev', false);
        $response->assertSee('data-calendar-next', false);
        $response->assertDontSee('app-calendar-range', false);
        $response->assertSee('dashboard\/manage\/calendar\/events', false);
        $response->assertSee('href="'.route('manage.calendar').'"', false);
        $response->assertSee('data-calendar-full-link', false);
        $response->assertSee('Pending');
        $response->assertSee('Booked');
        $response->assertSee('Calendar');
    }
}
