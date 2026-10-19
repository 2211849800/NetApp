<?php

namespace App\Services\Subscriber;

use App\Contracts\SubscriberProviderInterface;
use Illuminate\Support\Facades\Log;

class SubscriberDashboardService
{
    public function __construct(
        private SubscriberProviderInterface $provider
    ) {}

    public function build(string $contractNumber): array
    {
        try {
            $subscription = $this->provider->getSubscription($contractNumber);
            $usage = $this->provider->getUsage($contractNumber);
            $borrowing = $this->provider->getBorrowingStatus($contractNumber);
            $history = $this->provider->getUsageHistory($contractNumber, 30);

            $daysLeft = null;
            if ($subscription->expiresAt) {
                $expiresAt = \Carbon\Carbon::parse($subscription->expiresAt);
                $daysLeft = max(0, now()->diffInDays($expiresAt, false));
            }

            $insights = $this->calculateInsights($usage, $subscription->expiresAt, $history);

            return [
                'status' => 'ok',
                'updated_at' => now()->toIso8601String(),
                'subscription' => [
                    'package_name' => $subscription->packageName,
                    'price' => $subscription->packagePrice,
                    'status' => $subscription->status,
                    'expires_at' => $subscription->expiresAt ? \Carbon\Carbon::parse($subscription->expiresAt)->toDateString() : null,
                    'days_left' => $daysLeft,
                ],
                'usage' => [
                    'total_gb' => $usage->totalAllowanceGb,
                    'used_gb' => $usage->usedDataGb,
                    'remaining_gb' => $usage->remainingDataGb,
                    'percent' => min(100, max(0, $usage->usagePercentage)),
                ],
                'borrowing' => [
                    'status' => $borrowing->status,
                    'used_gb' => $borrowing->usedGb,
                    'limit_gb' => $borrowing->borrowingLimitGb,
                    'expires_at' => $borrowing->expiresAt ? \Carbon\Carbon::parse($borrowing->expiresAt)->toDateString() : null,
                ],
                'history' => $history,
                'insights' => $insights,
            ];
        } catch (\Throwable $e) {
            Log::error('Dashboard service error', [
                'contract_number' => $contractNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'status' => 'degraded',
                'updated_at' => now()->toIso8601String(),
                'subscription' => [
                    'package_name' => null,
                    'price' => null,
                    'status' => null,
                    'expires_at' => null,
                    'days_left' => null,
                ],
                'usage' => [
                    'total_gb' => 0.0,
                    'used_gb' => 0.0,
                    'remaining_gb' => 0.0,
                    'percent' => 0.0,
                ],
                'borrowing' => [
                    'status' => null,
                    'used_gb' => 0.0,
                    'limit_gb' => 0.0,
                    'expires_at' => null,
                ],
                'history' => [],
                'insights' => [
                    'avg_daily_gb' => 0.0,
                    'projected_depletion_date' => null,
                    'will_last_until_expiry' => null,
                ],
            ];
        }
    }

    private function calculateInsights($usage, ?string $expiresAt, array $history): array
    {
        $avgDailyGb = 0.0;
        $projectedDepletionDate = null;
        $willLastUntilExpiry = null;

        if (! empty($history)) {
            $last7Days = array_slice($history, -7);
            $totalLast7Days = array_sum(array_column($last7Days, 'used_gb'));
            $avgDailyGb = $totalLast7Days / 7;

            if ($avgDailyGb > 0 && $usage->remainingDataGb > 0) {
                $daysUntilDepletion = ceil($usage->remainingDataGb / $avgDailyGb);
                $projectedDepletionDate = now()->addDays($daysUntilDepletion)->toDateString();

                if ($expiresAt) {
                    $expiryDate = \Carbon\Carbon::parse($expiresAt);
                    $depletionDate = \Carbon\Carbon::parse($projectedDepletionDate);
                    $willLastUntilExpiry = $depletionDate->gte($expiryDate);
                }
            }
        }

        return [
            'avg_daily_gb' => round($avgDailyGb, 2),
            'projected_depletion_date' => $projectedDepletionDate,
            'will_last_until_expiry' => $willLastUntilExpiry,
        ];
    }
}
