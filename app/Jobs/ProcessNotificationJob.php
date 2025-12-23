<?php

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The notification instance.
     *
     * @var \App\Models\Notification
     */
    protected $notification;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Simulate sending notification based on channel
            // In a real application, this would call a specific service
            // e.g., Mail::to(...), or a push notification service.
            Log::info("Processing notification {$this->notification->id} for user {$this->notification->user_id} via {$this->notification->channel}.");

            sleep(2); // Simulate network latency

            $this->notification->update(['sent_at' => now()]);

            Log::info("Notification {$this->notification->id} sent successfully.");

        } catch (Exception $e) {
            $this->notification->update([
                'failed_at' => now(),
                'failure_reason' => $e->getMessage(),
            ]);
            Log::error("Failed to send notification {$this->notification->id}: " . $e->getMessage());
        }
    }
}
