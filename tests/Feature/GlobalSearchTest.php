<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingNotification;
use App\Models\Classroom;
use App\Models\ServicePackage;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_global_search_finds_operational_records(): void
    {
        Role::create(['name' => 'admin']);

        $admin = User::factory()->create([
            'name' => 'Atlas Admin',
            'email' => 'atlas.admin@example.com',
        ]);
        $admin->assignRole('admin');

        $room = Classroom::create([
            'name' => 'Atlas Studio',
            'slug' => 'atlas-studio',
            'location' => 'Ortigas',
            'capacity' => 12,
            'hourly_rate' => 800,
            'description' => 'Atlas search room.',
            'is_active' => true,
        ]);

        $package = ServicePackage::create([
            'name' => 'Atlas Package',
            'slug' => 'atlas-package',
            'description' => 'Atlas search package.',
            'base_price' => 1000,
            'is_active' => true,
        ]);

        $booking = Booking::create([
            'user_id' => $admin->id,
            'classroom_id' => $room->id,
            'service_package_id' => $package->id,
            'status' => BookingService::STATUS_PENDING,
            'booking_date' => now()->addDay()->toDateString(),
            'starts_at' => now()->addDay()->setTime(9, 0),
            'ends_at' => now()->addDay()->setTime(12, 0),
            'purpose' => 'Atlas planning workshop',
            'format' => 'Offline',
            'customer_name' => 'Atlas Customer',
            'contact' => 'atlas.customer@example.com',
        ]);

        BookingNotification::create([
            'booking_id' => $booking->id,
            'user_id' => $admin->id,
            'audience' => BookingNotification::AUDIENCE_STAFF,
            'channel' => 'mail',
            'notification_type' => 'App\\Notifications\\BookingSubmittedToStaff',
            'recipient' => 'staff',
            'subject' => 'Atlas notification',
            'message' => 'Atlas notification message.',
            'status' => BookingNotification::STATUS_SENT,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('search.index', ['q' => 'Atlas']));

        $response->assertOk();
        $response->assertSee('data-search-section="bookings"', false);
        $response->assertSee('Atlas Customer');
        $response->assertSee('Atlas Studio');
        $response->assertSee('data-search-section="packages"', false);
        $response->assertSee('Atlas Package');
        $response->assertSee('data-search-section="notifications"', false);
        $response->assertSee('Atlas notification message.');
        $response->assertSee('data-search-section="users"', false);
        $response->assertSee('Atlas Admin');

        $suggestions = $this->actingAs($admin)
            ->getJson(route('search.suggestions', ['q' => 'Atlas']));

        $suggestions->assertOk();
        $suggestions->assertJsonPath('query', 'Atlas');

        $sections = collect($suggestions->json('sections'));

        $this->assertTrue($sections->contains('key', 'bookings'));
        $this->assertTrue($sections->contains('key', 'rooms'));
        $this->assertTrue($sections->contains('key', 'packages'));
        $this->assertTrue($sections->contains('key', 'notifications'));
        $this->assertTrue($sections->contains('key', 'users'));
        $this->assertTrue(collect($sections->firstWhere('key', 'bookings')['items'])->contains(fn (array $item): bool => str_contains($item['label'], 'Atlas Customer')));
        $this->assertTrue(collect($sections->firstWhere('key', 'rooms')['items'])->contains(fn (array $item): bool => $item['label'] === 'Atlas Studio'));
        $this->assertTrue(collect($sections->firstWhere('key', 'packages')['items'])->contains(fn (array $item): bool => $item['label'] === 'Atlas Package'));
        $this->assertTrue(collect($sections->firstWhere('key', 'notifications')['items'])->contains(fn (array $item): bool => $item['label'] === 'Atlas notification'));
        $this->assertTrue(collect($sections->firstWhere('key', 'users')['items'])->contains(fn (array $item): bool => $item['label'] === 'Atlas Admin'));

        $fuzzyResponse = $this->actingAs($admin)
            ->get(route('search.index', ['q' => 'Atals']));

        $fuzzyResponse->assertOk();
        $fuzzyResponse->assertSee('Atlas Customer');
        $fuzzyResponse->assertSee('Atlas Studio');
        $fuzzyResponse->assertSee('Atlas Package');

        $fuzzySuggestions = $this->actingAs($admin)
            ->getJson(route('search.suggestions', ['q' => 'Atals']));

        $fuzzySuggestionSections = collect($fuzzySuggestions->json('sections'));

        $this->assertTrue($fuzzySuggestionSections->contains('key', 'bookings'));
        $this->assertTrue(collect($fuzzySuggestionSections->firstWhere('key', 'bookings')['items'])->contains(fn (array $item): bool => str_contains($item['label'], 'Atlas Customer')));
    }

    public function test_customer_global_search_is_limited_to_own_bookings_and_active_catalog(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();

        $activeRoom = Classroom::create([
            'name' => 'Atlas Public Room',
            'slug' => 'atlas-public-room',
            'capacity' => 12,
            'hourly_rate' => 800,
            'description' => 'Visible Atlas room.',
            'is_active' => true,
        ]);

        Classroom::create([
            'name' => 'Atlas Hidden Room',
            'slug' => 'atlas-hidden-room',
            'capacity' => 12,
            'hourly_rate' => 800,
            'description' => 'Inactive Atlas room.',
            'is_active' => false,
        ]);

        ServicePackage::create([
            'name' => 'Atlas Public Package',
            'slug' => 'atlas-public-package',
            'description' => 'Visible Atlas package.',
            'base_price' => 1000,
            'is_active' => true,
        ]);

        ServicePackage::create([
            'name' => 'Atlas Hidden Package',
            'slug' => 'atlas-hidden-package',
            'description' => 'Inactive Atlas package.',
            'base_price' => 1000,
            'is_active' => false,
        ]);

        Booking::create([
            'user_id' => $customer->id,
            'classroom_id' => $activeRoom->id,
            'status' => BookingService::STATUS_PENDING,
            'booking_date' => now()->addDay()->toDateString(),
            'starts_at' => now()->addDay()->setTime(9, 0),
            'ends_at' => now()->addDay()->setTime(12, 0),
            'purpose' => 'Atlas own booking',
            'format' => 'Offline',
            'customer_name' => 'Atlas Owner',
            'contact' => 'atlas.owner@example.com',
        ]);

        Booking::create([
            'user_id' => $otherCustomer->id,
            'classroom_id' => $activeRoom->id,
            'status' => BookingService::STATUS_PENDING,
            'booking_date' => now()->addDay()->toDateString(),
            'starts_at' => now()->addDay()->setTime(13, 0),
            'ends_at' => now()->addDay()->setTime(14, 0),
            'purpose' => 'Atlas private booking',
            'format' => 'Offline',
            'customer_name' => 'Atlas Other',
            'contact' => 'atlas.other@example.com',
        ]);

        $response = $this->actingAs($customer)
            ->get(route('search.index', ['q' => 'Atlas']));

        $response->assertOk();
        $response->assertSee('Atlas Owner');
        $response->assertDontSee('Atlas Other');
        $response->assertSee('Atlas Public Room');
        $response->assertDontSee('Atlas Hidden Room');
        $response->assertSee('Atlas Public Package');
        $response->assertDontSee('Atlas Hidden Package');
        $response->assertDontSee('data-search-section="notifications"', false);
        $response->assertDontSee('data-search-section="users"', false);

        $suggestions = $this->actingAs($customer)
            ->getJson(route('search.suggestions', ['q' => 'Atlas']));

        $suggestions->assertOk();

        $sections = collect($suggestions->json('sections'));
        $labels = $sections
            ->flatMap(fn (array $section) => collect($section['items'])->pluck('label'))
            ->implode(' ');

        $this->assertTrue($sections->contains('key', 'bookings'));
        $this->assertTrue($sections->contains('key', 'rooms'));
        $this->assertTrue($sections->contains('key', 'packages'));
        $this->assertFalse($sections->contains('key', 'notifications'));
        $this->assertFalse($sections->contains('key', 'users'));
        $this->assertStringContainsString('Atlas Owner', $labels);
        $this->assertStringNotContainsString('Atlas Other', $labels);
        $this->assertStringContainsString('Atlas Public Room', $labels);
        $this->assertStringNotContainsString('Atlas Hidden Room', $labels);
        $this->assertStringContainsString('Atlas Public Package', $labels);
        $this->assertStringNotContainsString('Atlas Hidden Package', $labels);

        $fuzzySuggestions = $this->actingAs($customer)
            ->getJson(route('search.suggestions', ['q' => 'Atals']));

        $fuzzySections = collect($fuzzySuggestions->json('sections'));
        $fuzzyLabels = $fuzzySections
            ->flatMap(fn (array $section) => collect($section['items'])->pluck('label'))
            ->implode(' ');

        $this->assertStringContainsString('Atlas Owner', $fuzzyLabels);
        $this->assertStringNotContainsString('Atlas Other', $fuzzyLabels);
    }

    public function test_global_search_handles_wider_name_typos(): void
    {
        Role::create(['name' => 'admin']);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        User::factory()->create([
            'name' => 'Khirvie Santos',
            'email' => 'khirvie.santos@example.com',
        ]);

        $room = Classroom::create([
            'name' => 'Focus Room',
            'slug' => 'focus-room',
            'capacity' => 8,
            'hourly_rate' => 700,
            'description' => 'Small room.',
            'is_active' => true,
        ]);

        Booking::create([
            'user_id' => $admin->id,
            'classroom_id' => $room->id,
            'status' => BookingService::STATUS_PENDING,
            'booking_date' => now()->addDay()->toDateString(),
            'starts_at' => now()->addDay()->setTime(10, 0),
            'ends_at' => now()->addDay()->setTime(12, 0),
            'purpose' => 'Advising session',
            'format' => 'Offline',
            'customer_name' => 'Khirvie Santos',
            'contact' => 'khirvie.santos@example.com',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('search.index', ['q' => 'khriere']));

        $response->assertOk();
        $response->assertSee('data-search-correction', false);
        $response->assertSee('Did you mean');
        $response->assertSee('Khirvie');
        $response->assertSee('Khirvie Santos');

        $suggestions = $this->actingAs($admin)
            ->getJson(route('search.suggestions', ['q' => 'khriere']));

        $suggestions->assertOk();
        $suggestions->assertJsonPath('correction.query', 'Khirvie');
        $suggestions->assertJsonPath('correction.label', 'Khirvie Santos');

        $labels = collect($suggestions->json('sections'))
            ->flatMap(fn (array $section) => collect($section['items'])->pluck('label'))
            ->implode(' ');

        $this->assertStringContainsString('Khirvie Santos', $labels);
    }
}
