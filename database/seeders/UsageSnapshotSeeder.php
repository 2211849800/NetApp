<?php

namespace Database\Seeders;

use App\Models\UsageSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsageSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        $subscribers = DB::table('mock_subscribers')->get();

        foreach ($subscribers as $subscriber) {
            $contractNumber = $subscriber->contract_number;

            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();

                $isWeekend = in_array(now()->subDays($i)->dayOfWeek, [5, 6]); // Friday (5) and Saturday (6) in Arabic countries
                $baseUsage = $isWeekend ? mt_rand(500, 4500) / 1000 : mt_rand(400, 3000) / 1000;

                UsageSnapshot::updateOrInsert(
                    [
                        'contract_number' => $contractNumber,
                        'usage_date' => $date,
                    ],
                    [
                        'used_gb' => $baseUsage,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
