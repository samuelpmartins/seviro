<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Events\OrderStatusChanged;
use App\Listeners\NotifyOrderCreated;
use App\Listeners\NotifyOrderFinished;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        OrderCreated::class => [
            NotifyOrderCreated::class,
        ],
        OrderPaid::class => [
            NotifyOrderCreated::class,
        ],
        OrderStatusChanged::class => [
            NotifyOrderFinished::class,
        ],
        'App\Events\DemoRequestApproved' => [
            'App\Listeners\SendDemoRequestApprovedEmail',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
