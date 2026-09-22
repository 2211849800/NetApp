<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            StaffUserSeeder::class,
            MockPackageSeeder::class,
            MockSubscriberSeeder::class,
            OperationalDemoSeeder::class,
        ]);

        // Legacy test subscriber (API/mobile — not for web panel)
        $subscriber = User::updateOrCreate(
            ['email' => 'subscriber@example.com'],
            [
                'name' => 'Test Subscriber',
                'phone' => '0900000099',
                'password' => bcrypt('password'),
                'type' => 'subscriber',
            ],
        );
        $subscriber->assignRole('subscriber');
    }
}
