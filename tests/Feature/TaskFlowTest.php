<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function auth()
    {
        $u = User::factory()->create(['password' => bcrypt('secret')]);
        $t = $this->postJson('/api/login', ['email'=>$u->email,'password'=>'secret'])->json('token');
        return [$u, $t];
    }

    public function test_author_can_create_update_delete_task_and_see_it_in_list(): void
    {
        [$user, $token] = $this->auth();

        // Create
        $create = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/tasks', ['title'=>'First Task', 'description'=>'hello'])
            ->assertCreated();

        $taskId = $create->json('id');

        // List
        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonFragment(['id' => $taskId, 'title' => 'First Task']);

        // Update
        $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/tasks/{$taskId}", ['title'=>'Updated'])
            ->assertOk()
            ->assertJsonFragment(['title' => 'Updated']);

        // Delete
        $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/tasks/{$taskId}")
            ->assertNoContent();
    }

    public function test_assigned_to_me_endpoint(): void
    {
        [$author, $token] = $this->auth();
        $assignee = User::factory()->create();

        // Create task by author
        $taskId = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/tasks', ['title'=>'To Assign'])
            ->json('id');

        // Assign to $assignee
        $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/tasks/{$taskId}/assign", ['assignee_id' => $assignee->id])
            ->assertOk();

        // Login as assignee
        $assigneeToken = $this->postJson('/api/login', [
            'email' => $assignee->email, 'password' => 'password' // default factory password is 'password'
        ])->json('token');

        // Check assigned-to-me list
        $this->withHeader('Authorization', "Bearer $assigneeToken")
            ->getJson('/api/me/assigned-tasks')
            ->assertOk()
            ->assertJsonFragment(['id' => $taskId]);
    }
}