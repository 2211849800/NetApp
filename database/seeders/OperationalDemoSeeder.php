<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\MockSubscriber;
use App\Models\Payment;
use App\Models\RechargeRequest;
use App\Models\SubscriptionRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class OperationalDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $employee = User::where('username', 'employee')->first();

        $samples = MockSubscriber::query()
            ->whereIn('contract_number', ['TEST-100001', 'TEST-100002', 'TEST-100003', 'TEST-100004'])
            ->get()
            ->keyBy('contract_number');

        if ($samples->isEmpty()) {
            return;
        }

        $first = $samples->get('TEST-100001');
        $second = $samples->get('TEST-100002');
        $third = $samples->get('TEST-100003');
        $fourth = $samples->get('TEST-100004');

        if ($first) {
            RechargeRequest::updateOrCreate(
                ['contract_number' => $first->contract_number, 'status' => 'pending', 'package_id' => 'PKG-100'],
                [
                    'subscriber_name' => $first->name,
                    'package_name' => 'ميقا 100G',
                    'amount' => 100,
                    'payment_method' => 'lypay',
                ],
            );

            Payment::updateOrCreate(
                ['reference' => 'PAY-LYPAY-100001'],
                [
                    'contract_number' => $first->contract_number,
                    'subscriber_name' => $first->name,
                    'gateway' => 'lypay',
                    'amount' => 100,
                    'status' => 'completed',
                    'paid_at' => now()->subHours(2),
                    'processed_by' => $employee?->id,
                ],
            );

            Complaint::updateOrCreate(
                ['contract_number' => $first->contract_number, 'subject' => 'بطء في السرعة المسائية'],
                [
                    'subscriber_name' => $first->name,
                    'type' => 'ضعف في السرعة',
                    'priority' => 'medium',
                    'description' => 'تنخفض السرعة بعد الساعة الثامنة مساءً.',
                    'status' => 'open',
                    'assigned_to' => $employee?->id,
                ],
            );
        }

        if ($second) {
            RechargeRequest::updateOrCreate(
                ['contract_number' => $second->contract_number, 'status' => 'approved', 'package_id' => 'PKG-150'],
                [
                    'subscriber_name' => $second->name,
                    'package_name' => 'ميقا 150G',
                    'amount' => 150,
                    'payment_method' => 'onepay',
                    'processed_by' => $employee?->id,
                    'processed_at' => now()->subDay(),
                    'notes' => 'تم الشحن بعد التحقق من الإيصال.',
                ],
            );

            Payment::updateOrCreate(
                ['reference' => 'PAY-ONEPAY-100002'],
                [
                    'contract_number' => $second->contract_number,
                    'subscriber_name' => $second->name,
                    'gateway' => 'onepay',
                    'amount' => 150,
                    'status' => 'completed',
                    'paid_at' => now()->subDay(),
                    'processed_by' => $employee?->id,
                ],
            );
        }

        if ($third) {
            RechargeRequest::updateOrCreate(
                ['contract_number' => $third->contract_number, 'status' => 'pending', 'package_id' => 'PKG-70'],
                [
                    'subscriber_name' => $third->name,
                    'package_name' => 'ميقا 70G',
                    'amount' => 70,
                    'payment_method' => 'bank_transfer',
                    'notes' => 'بانتظار مطابقة التحويل البنكي.',
                ],
            );

            Payment::updateOrCreate(
                ['reference' => 'PAY-BANK-100003'],
                [
                    'contract_number' => $third->contract_number,
                    'subscriber_name' => $third->name,
                    'gateway' => 'bank_transfer',
                    'amount' => 70,
                    'status' => 'pending',
                ],
            );

            Complaint::updateOrCreate(
                ['contract_number' => $third->contract_number, 'subject' => 'انقطاع الخدمة'],
                [
                    'subscriber_name' => $third->name,
                    'type' => 'انقطاع',
                    'priority' => 'high',
                    'description' => 'الخدمة غير مستقرة منذ الصباح.',
                    'status' => 'in_progress',
                    'assigned_to' => $employee?->id,
                ],
            );
        }

        if ($fourth) {
            Payment::updateOrCreate(
                ['reference' => 'PAY-CASH-100004'],
                [
                    'contract_number' => $fourth->contract_number,
                    'subscriber_name' => $fourth->name,
                    'gateway' => 'cash',
                    'amount' => 70,
                    'status' => 'completed',
                    'paid_at' => now()->subDays(3),
                    'processed_by' => $employee?->id,
                ],
            );
        }

        SubscriptionRequest::updateOrCreate(
            ['national_id' => '119900123456', 'phone' => '0911112233'],
            [
                'applicant_name' => 'يوسف علي الكيلاني',
                'email' => 'yousef@example.com',
                'city' => 'طرابلس',
                'address' => 'حي الأندلس',
                'package_id' => 'PKG-100',
                'package_name' => 'ميقا 100G',
                'status' => 'pending',
            ],
        );

        SubscriptionRequest::updateOrCreate(
            ['national_id' => '219850987654', 'phone' => '0922223344'],
            [
                'applicant_name' => 'هدى سالم القماطي',
                'city' => 'بنغازي',
                'package_id' => 'PKG-70',
                'package_name' => 'ميقا 70G',
                'status' => 'pending',
            ],
        );
    }
}
