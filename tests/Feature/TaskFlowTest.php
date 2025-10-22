<?php

namespace Tests\Feature;

use App\Jobs\NewCommentOnTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TaskFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_task_and_comment_and_queue_job()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        // Create task
        $create = $this->withToken($token)->postJson('/api/tasks', [
            'title' => 'My Task'
        ]);
        $create->assertCreated();
        $taskId = $create->json('id');

        // Assign self
        $assign = $this->withToken($token)->postJson("/api/tasks/{$taskId}/assign", [
            'assignee_id' => $user->id
        ]);
        $assign->assertOk();

        // Comment (queue job)
        Queue::fake();
        $comment = $this->withToken($token)->postJson("/api/tasks/{$taskId}/comments", [
            'body' => 'Nice!'
        ]);
        $comment->assertCreated();
        Queue::assertPushed(NewCommentOnTask::class);
    }
}
