<?php

namespace Tests\Feature;

use App\Jobs\NewCommentOnTask;
use App\Models\User;
use App\Notifications\NewCommentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentAndNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_queues_job_and_notifies_author_via_mail(): void
    {
        $author    = User::factory()->create();
        $commenter = User::factory()->create();

        // Author creates the task
        Sanctum::actingAs($author);
        $taskId = $this->postJson('/api/tasks', ['title' => 'Notify Author'])
            ->assertCreated()
            ->json('id');

        // Author assigns the commenter so they can view/comment (per typical policy)
        $this->postJson("/api/tasks/{$taskId}/assign", ['assignee_id' => $commenter->id])
             ->assertOk();

        // Fake queue & notifications
        Queue::fake();
        Notification::fake();

        // Comment as the assignee (commenter)
        Sanctum::actingAs($commenter);
        $commentId = $this->postJson("/api/tasks/{$taskId}/comments", ['body' => 'hello'])
        ->assertCreated()   // 201
        ->json('id');

        // 1) Assert the job was queued with correct payload
        $capturedJob = null;
        Queue::assertPushed(NewCommentOnTask::class, function (NewCommentOnTask $job) use ($commentId, &$capturedJob) {
            $capturedJob = $job;
            return $job->commentId === $commentId;
        });

        // 2) Execute the queued job to complete the flow (Queue::fake prevents auto-run)
        //    This will load the Comment, find the task author, and send NewCommentNotification via 'mail'.
        $this->assertNotNull($capturedJob, 'Expected NewCommentOnTask to be queued.');
        $capturedJob->handle();

        // 3) Author should be notified; commenter should NOT
        Notification::assertSentTo(
            $author,
            NewCommentNotification::class,
            function (NewCommentNotification $notification, array $channels) use ($commentId) {
                // Verify the notification references the same comment and includes the 'mail' channel
                return in_array('mail', $channels, true)
                    && $notification->comment->id === $commentId;
            }
        );

        Notification::assertNotSentTo($commenter, NewCommentNotification::class);
    }
}