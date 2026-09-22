<?php

namespace App\Providers;

use App\Contracts\SubscriberProviderInterface;
use App\Integrations\Subscriber\Mock\MockSubscriberProvider;
use Illuminate\Support\ServiceProvider;

class SubscriberServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SubscriberProviderInterface::class, function ($app) {
            $provider = config('subscriber.provider', 'mock');

            return match ($provider) {
                'mock' => $app->make(MockSubscriberProvider::class),
                // 'adv' => $app->make(AdvSubscriberProvider::class),
                default => $app->make(MockSubscriberProvider::class),
            };
        });
    }
}
