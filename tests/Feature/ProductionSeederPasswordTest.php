<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Database\Seeders\StaffUserSeeder;
use RuntimeException;
use Tests\TestCase;

class ProductionSeederPasswordTest extends TestCase
{
    public function test_database_seeder_refuses_default_passwords_in_production(): void
    {
        $this->assertProductionSeederRequiresDemoPassword(DatabaseSeeder::class);
    }

    public function test_staff_seeder_refuses_default_passwords_in_production(): void
    {
        $this->assertProductionSeederRequiresDemoPassword(StaffUserSeeder::class);
    }

    /** @param class-string $seederClass */
    private function assertProductionSeederRequiresDemoPassword(string $seederClass): void
    {
        $wasSet = getenv('DEMO_PASSWORD') !== false;
        $oldValue = getenv('DEMO_PASSWORD');
        $oldEnv = $_ENV['DEMO_PASSWORD'] ?? null;
        $hadEnv = array_key_exists('DEMO_PASSWORD', $_ENV);
        $oldServer = $_SERVER['DEMO_PASSWORD'] ?? null;
        $hadServer = array_key_exists('DEMO_PASSWORD', $_SERVER);

        putenv('DEMO_PASSWORD=');
        $_ENV['DEMO_PASSWORD'] = '';
        $_SERVER['DEMO_PASSWORD'] = '';
        $this->app['env'] = 'production';

        try {
            try {
                $this->app->make($seederClass)->run();
                $this->fail('Expected production seeding without DEMO_PASSWORD to be rejected.');
            } catch (RuntimeException $exception) {
                $this->assertStringContainsString('DEMO_PASSWORD must be configured', $exception->getMessage());
            }
        } finally {
            if ($wasSet) {
                putenv('DEMO_PASSWORD='.$oldValue);
            } else {
                putenv('DEMO_PASSWORD');
            }

            if ($hadEnv) {
                $_ENV['DEMO_PASSWORD'] = $oldEnv;
            } else {
                unset($_ENV['DEMO_PASSWORD']);
            }

            if ($hadServer) {
                $_SERVER['DEMO_PASSWORD'] = $oldServer;
            } else {
                unset($_SERVER['DEMO_PASSWORD']);
            }

            $this->app['env'] = 'testing';
        }
    }
}
