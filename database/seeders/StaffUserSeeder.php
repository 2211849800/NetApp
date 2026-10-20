<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Seeds development/test staff users for web authentication.
 *
 * Admin login:
 *   username: admin
 *   email:    admin@netapp.com
 *   password: DEMO_PASSWORD env var (required in production; local default is password)
 */
class StaffUserSeeder extends Seeder
{
    public function run(): void
    {
        $demoPassword = env('DEMO_PASSWORD');

        if (app()->environment('production') && (! is_string($demoPassword) || trim($demoPassword) === '')) {
            throw new RuntimeException('DEMO_PASSWORD must be configured before seeding staff users in production.');
        }

        $password = Hash::make($demoPassword ?: 'password');

        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'مدير النظام',
                'email' => 'admin@netapp.com',
                'phone' => null,
                'password' => $password,
                'type' => 'admin',
                'status' => UserStatus::Active,
                'is_active' => true,
            ],
        );
        $admin->roles()->sync([]);
        $admin->assignRole('admin');

        $employee = User::updateOrCreate(
            ['username' => 'employee'],
            [
                'name' => 'Employee User (Test)',
                'email' => 'employee@example.com',
                'phone' => null,
                'password' => $password,
                'type' => 'employee',
                'status' => UserStatus::Active,
                'is_active' => true,
            ],
        );
        $employee->roles()->sync([]);
        $employee->assignRole('employee');

        $supervisor = User::updateOrCreate(
            ['username' => 'supervisor'],
            [
                'name' => 'Supervisor User (Test)',
                'email' => 'supervisor@example.com',
                'phone' => null,
                'password' => $password,
                'type' => 'employee',
                'status' => UserStatus::Active,
                'is_active' => true,
            ],
        );
        $supervisor->roles()->sync([]);
        $supervisor->assignRole('supervisor');
    }
}
