<?php

namespace Tests\Unit;

use App\Jobs\ProcessNotificationJob;
use App\Models\Notification;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function creating_a_notification_dispatches_process_notification_job()
    {
        Queue::fake();

        $school = School::factory()->create();
        $user = User::factory()->create(['school_id' => $school->id]);

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'test.notification',
            'channel' => 'email',
            'content' => 'This is a test notification.',
        ]);

        Queue::assertPushed(ProcessNotificationJob::class, function ($job) use ($notification) {
            return $job->notification->id === $notification->id;
        });
    }
}
