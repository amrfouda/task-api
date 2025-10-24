<?php

namespace Tests\Unit;

use App\Jobs\NewCommentOnTask;
use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CommentNotificationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_can_be_dispatched_for_comment()
    {
        $author  = User::factory()->create();
        $commenter = User::factory()->create();
        $task    = Task::factory()->create(['author_id' => $author->id]);

        $comment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $commenter->id,
            'body'    => 'Great job!',
        ]);

        Bus::fake();

        Bus::dispatch(new NewCommentOnTask($comment->id));

        Bus::assertDispatched(NewCommentOnTask::class, function ($job) use ($comment) {
            return $job->commentId === $comment->id;
        });
    }
}