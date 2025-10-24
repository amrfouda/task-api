<?php

namespace Tests\Feature;

use App\Jobs\NewCommentOnTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CommentAndNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_posting_comment_dispatches_job_to_notify_author(): void
    {
        $author = User::factory()->create();
        $authorToken = $this->postJson('/api/login', [
            'email'=>$author->email, 'password'=>'password'
        ])->json('token');

        // Author creates a task
        $taskId = $this->withHeader('Authorization', "Bearer $authorToken")
            ->postJson('/api/tasks', ['title' => 'Notify Me'])
            ->json('id');

        // Another user comments
        $commenter = User::factory()->create();
        $commenterToken = $this->postJson('/api/login', [
            'email'=>$commenter->email, 'password'=>'password'
        ])->json('token');

        Bus::fake(); // capture queued jobs

        $this->withHeader('Authorization', "Bearer $commenterToken")
            ->postJson("/api/tasks/{$taskId}/comments", ['body' => 'Great job!'])
            ->assertCreated();

        Bus::assertDispatched(NewCommentOnTask::class);
    }
}