<?php

namespace Database\Seeders;

use App\Enums\RecordStatus;
use App\Models\Package;
use Illuminate\Database\Seeder;

/**
 * Seeds ISP Packages matching the Subscriber Provider catalog.
 * Designed strictly for development/testing environments.
 */
class MockPackageSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $packages = [
            [
                'name' => 'ميقا 70G',
                'description' => 'باقة إنترنت منزلي سريعة ومناسبة للاستخدام اليومي الخفيف',
                'price' => 70.00,
                'data_allowance' => '79 GB',
                'duration_days' => 30,
                'status' => RecordStatus::Active,
            ],
            [
                'name' => 'ميقا 100G',
                'description' => 'باقة إنترنت متوازنة تناسب العائلات الصغيرة وتصفح الفيديو بدقة عالية',
                'price' => 100.00,
                'data_allowance' => '100 GB',
                'duration_days' => 30,
                'status' => RecordStatus::Active,
            ],
            [
                'name' => 'ميقا 150G',
                'description' => 'باقة فائقة السرعة للاستخدام العائلي المكثف والألعاب عبر الإنترنت',
                'price' => 150.00,
                'data_allowance' => '150 GB',
                'duration_days' => 30,
                'status' => RecordStatus::Active,
            ],
            [
                'name' => 'ميقا 300G',
                'description' => 'باقة للأعمال الصغيرة والتحميل الثقيل وسرعات قصوى دون انقطاع',
                'price' => 300.00,
                'data_allowance' => '300 GB',
                'duration_days' => 30,
                'status' => RecordStatus::Active,
            ],
            [
                'name' => 'ميقا 500G',
                'description' => 'باقة بريميوم غير محدودة السرعة مع أعلى حصة بيانات للمحترفين',
                'price' => 500.00,
                'data_allowance' => '500 GB',
                'duration_days' => 30,
                'status' => RecordStatus::Active,
            ],
        ];

        foreach ($packages as $pkg) {
            Package::updateOrCreate(
                ['name' => $pkg['name']],
                $pkg,
            );
        }
    }
}
