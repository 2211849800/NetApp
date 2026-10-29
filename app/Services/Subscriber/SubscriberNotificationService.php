<?php

namespace App\Services\Subscriber;

use App\Models\OperationalNotification;
use App\Models\User;

class SubscriberNotificationService
{
    public function sendToContract(
        string $contractNumber,
        string $title,
        string $message,
        string $type = 'info',
        ?string $notificationKey = null,
        ?User $sender = null,
    ): ?OperationalNotification {
        $recipient = User::subscribers()
            ->where(function ($query) use ($contractNumber) {
                $query->where('contract_number', $contractNumber)
                    ->orWhere('username', $contractNumber);
            })
            ->first();

        if (! $recipient) {
            return null;
        }

        $attributes = [
            'user_id' => $recipient->id,
            'contract_number' => $contractNumber,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'created_by' => $sender?->id,
        ];

        if ($notificationKey !== null) {
            return OperationalNotification::firstOrCreate(
                ['notification_key' => $notificationKey],
                $attributes,
            );
        }

        return OperationalNotification::create($attributes);
    }

    public function notifyRecharge(string $contractNumber, string $packageName, string $key, ?User $sender = null): ?OperationalNotification
    {
        return $this->sendToContract(
            $contractNumber,
            'تم شحن اشتراكك',
            "تم شحن اشتراكك بنجاح بباقة {$packageName}.",
            'success',
            'recharge:'.$contractNumber.':'.$key,
            $sender,
        );
    }
}
