<?php

namespace Database\Seeders;

use App\Models\MockSubscriber;
use App\Models\MockSubscriberSession;
use Illuminate\Database\Seeder;

class MockSubscriberSessionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (MockSubscriber::query()->get() as $subscriber) {
            for ($i = 0; $i < 3; $i++) {
                $startedAt = now()->subDays($i)->subHours(2 + $i);
                $isCurrent = $i === 0 && $subscriber->status === 'ACTIVE';

                MockSubscriberSession::updateOrCreate(
                    ['session_id' => $subscriber->contract_number.'-SESSION-'.($i + 1)],
                    [
                        'contract_number' => $subscriber->contract_number,
                        'ip_address' => '10.20.'.($subscriber->id % 255).'.'.(10 + $i),
                        'mac_address' => sprintf('02:00:%02X:%02X:%02X:%02X', $subscriber->id % 256, $i, 0, 1),
                        'access_point' => 'AirMax Sector '.(1 + ($subscriber->id % 4)),
                        'started_at' => $startedAt,
                        'ended_at' => $isCurrent ? null : $startedAt->copy()->addHours(2 + $i),
                        'download_gb' => 1.2 + ($i * 0.7),
                        'upload_gb' => 0.15 + ($i * 0.05),
                    ],
                );
            }
        }
    }
}
