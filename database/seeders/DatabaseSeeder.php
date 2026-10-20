<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $demoPassword = env('DEMO_PASSWORD');

        if (app()->environment('production') && (! is_string($demoPassword) || trim($demoPassword) === '')) {
            throw new RuntimeException('DEMO_PASSWORD must be configured before seeding demo users in production.');
        }

        $this->call([
            RolePermissionSeeder::class,
            StaffUserSeeder::class,
            MockPackageSeeder::class,
            MockSubscriberSeeder::class,
            OperationalDemoSeeder::class,
            UsageSnapshotSeeder::class,
        ]);

        // Legacy test subscriber (API/mobile — not for web panel)
        $subscriber = User::updateOrCreate(
            ['email' => 'subscriber@example.com'],
            [
                'name' => 'Test Subscriber',
                'phone' => '0900000099',
                'password' => bcrypt($demoPassword ?: 'password'),
                'type' => 'subscriber',
            ],
        );
        $subscriber->assignRole('subscriber');
    }
}
