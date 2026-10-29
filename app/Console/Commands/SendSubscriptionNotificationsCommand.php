<?php

namespace App\Console\Commands;

use App\Models\MockSubscriber;
use App\Services\Subscriber\SubscriberNotificationService;
use Illuminate\Console\Command;

class SendSubscriptionNotificationsCommand extends Command
{
    protected $signature = 'subscribers:send-subscription-notifications';

    protected $description = 'Send subscriber notifications for upcoming and expired subscriptions.';

    public function handle(SubscriberNotificationService $notifications): int
    {
        $reminderDate = today()->addDays(3);
        $expiring = MockSubscriber::whereDate('expires_at', $reminderDate)->get();
        $expired = MockSubscriber::whereDate('expires_at', today())->get();

        foreach ($expiring as $subscriber) {
            $notifications->sendToContract(
                $subscriber->contract_number,
                'اشتراكك على وشك الانتهاء',
                'متبقي 3 أيام على انتهاء اشتراكك. جدد باقتك لتستمر الخدمة دون انقطاع.',
                'warning',
                'expiry-reminder:'.$subscriber->contract_number.':'.$subscriber->expires_at->toDateString(),
            );
        }

        foreach ($expired as $subscriber) {
            $notifications->sendToContract(
                $subscriber->contract_number,
                'انتهى اشتراكك',
                'انتهى اشتراكك اليوم. جدد باقتك لإعادة تفعيل الخدمة.',
                'alert',
                'expired:'.$subscriber->contract_number.':'.$subscriber->expires_at->toDateString(),
            );
        }

        $this->info('Sent '.($expiring->count() + $expired->count()).' subscription notifications.');

        return self::SUCCESS;
    }
}
