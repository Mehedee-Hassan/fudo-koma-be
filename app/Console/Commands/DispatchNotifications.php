<?php

namespace App\Console\Commands;

use App\Services\NotificationDispatcher;
use App\Services\PushGateway;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class DispatchNotifications extends Command
{
    protected $signature = 'notifications:dispatch';

    protected $description = 'Create update and nearby notifications and deliver pending push messages';

    public function handle(NotificationDispatcher $dispatcher, PushGateway $gateway): int
    {
        $lock = Cache::lock('notifications:dispatch', 7200);
        if (! $lock->get()) {
            $this->info('Another dispatcher is running.');

            return self::SUCCESS;
        }
        try {
            $dispatcher->fanout();
            $dispatcher->nearby();
            $dispatcher->deliver($gateway);
        } finally {
            $lock->release();
        }
        $this->info('Notification dispatch complete.');

        return self::SUCCESS;
    }
}
