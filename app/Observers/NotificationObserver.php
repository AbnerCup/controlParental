<?php

namespace App\Observers;

use App\Jobs\ProcessNotificationJob;
use App\Models\Notification;

class NotificationObserver
{
    /**
     * Handle the Notification "created" event.
     *
     * @param  \App\Models\Notification  $notification
     * @return void
     */
    public function created(Notification $notification)
    {
        ProcessNotificationJob::dispatch($notification);
    }
}
