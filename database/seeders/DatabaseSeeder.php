<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ServicePackage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $permissions = [
            'manage users',
            'manage roles',
            'manage classrooms',
            'manage packages',
            'view bookings',
            'manage bookings',
            'approve bookings',
            'reject bookings',
            'cancel bookings',
            'reschedule bookings',
            'add internal notes',
            'create bookings',
            'view own bookings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $staff = Role::firstOrCreate(['name' => 'staff']);
        $customer = Role::firstOrCreate(['name' => 'customer']);

        $admin->syncPermissions($permissions);
        $staff->syncPermissions([
            'view bookings',
            'manage bookings',
            'approve bookings',
            'reject bookings',
            'cancel bookings',
            'reschedule bookings',
            'add internal notes',
        ]);
        $customer->syncPermissions([
            'create bookings',
            'view own bookings',
        ]);

        $adminUser = User::factory()->create([
            'name' => 'ICAN Admin',
            'email' => env('SEED_ADMIN_EMAIL', 'admin@icaneduspace.test'),
            'password' => env('SEED_ADMIN_PASSWORD', 'change-this-password'),
        ]);
        $adminUser->assignRole('admin');

        $staffUser = User::factory()->create([
            'name' => 'Booking Staff',
            'email' => env('SEED_STAFF_EMAIL', 'staff@icaneduspace.test'),
            'password' => env('SEED_STAFF_PASSWORD', 'change-this-password'),
        ]);
        $staffUser->assignRole('staff');

        $customerUser = User::factory()->create([
            'name' => 'Sample Customer',
            'email' => env('SEED_CUSTOMER_EMAIL', 'customer@icaneduspace.test'),
            'password' => env('SEED_CUSTOMER_PASSWORD', 'change-this-password'),
        ]);
        $customerUser->assignRole('customer');

        Classroom::insert([
            [
                'name' => '11F AI Hub',
                'slug' => '11f-ai-hub',
                'location' => '11F Eduspace',
                'address' => 'Strata 100, Emerald Ave., Ortigas Center, Pasig City, Philippines',
                'capacity' => 24,
                'hourly_rate' => 800,
                'description' => 'Main classroom for AI workshops, seminars, presentations, and hybrid classes.',
                'image_url' => '/media/AICognitionRoom.jpeg',
                'gallery' => json_encode([
                    '/media/AICognitionRoom.jpeg',
                    '/media/AICognitionDoor.jpeg',
                    '/media/AICOGNITIONLEFTCORNERVIEW.jpeg',
                    '/media/AICognitionRightCornerView.jpeg',
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '16F Mentoring Zone',
                'slug' => '16f-mentoring-zone',
                'location' => '16F Focus Zone',
                'address' => 'Strata 100, Emerald Ave., Ortigas Center, Pasig City, Philippines',
                'capacity' => 8,
                'hourly_rate' => 500,
                'description' => 'Focused space for tutoring, consultation, and small-group mentoring.',
                'image_url' => '/media/A2RoomView.jpeg',
                'gallery' => json_encode([
                    '/media/A2RoomView.jpeg',
                    '/media/A2InsideRoomView.jpeg',
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dual Campus Access',
                'slug' => 'dual-campus-access',
                'location' => '11F + 16F',
                'address' => 'Strata 100, Emerald Ave., Ortigas Center, Pasig City, Philippines',
                'capacity' => 32,
                'hourly_rate' => 1500,
                'description' => 'Linked room setup for institution programs and larger sessions.',
                'image_url' => '/media/A3RoomView.jpeg',
                'gallery' => json_encode([
                    '/media/A3RoomView.jpeg',
                    '/media/A3RoomView2.jpeg',
                    '/media/A3Entry.jpeg',
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        ServicePackage::insert([
            [
                'name' => 'Space Only',
                'slug' => 'space-only',
                'description' => 'Basic classroom booking for study, tutoring, meetings, and light room use.',
                'base_price' => 200,
                'duration_minutes' => 60,
                'included_services' => json_encode(['Room use', 'Basic display setup', 'Staff confirmation']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'AI Class Ready',
                'slug' => 'ai-class-ready',
                'description' => 'Classroom booking with AI class support, templates, and onsite flow.',
                'base_price' => 800,
                'duration_minutes' => 120,
                'included_services' => json_encode(['AI class option', 'Presentation setup', 'Basic refreshments']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Institution Partner',
                'slug' => 'institution-partner',
                'description' => 'Consultation-based package for schools, teams, and recurring sessions.',
                'base_price' => 1500,
                'duration_minutes' => 240,
                'included_services' => json_encode(['Monthly slot planning', 'Custom operations plan', 'Feedback flow']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
