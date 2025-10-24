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
        // Setup Author and Assignee
        $author   = User::factory()->create();
        $assignee = User::factory()->create();

        // 1. Create task by author (using actingAs)
        $taskResponse = $this->actingAs($author)
            ->postJson('/api/tasks', ['title'=>'To Assign'])
            ->assertCreated();

        $taskId = $taskResponse->json('id');

        // 2. Assign to $assignee (using actingAs)
        $this->actingAs($author)
            ->postJson("/api/tasks/{$taskId}/assign", ['assignee_id' => $assignee->id])
            ->assertOk();

        // 3. Check assigned-to-me list (using actingAs)
        $this->actingAs($assignee)
            ->getJson('/api/me/assigned-tasks')
            ->assertOk()
            ->assertJsonFragment(['id' => $taskId]);
    }
}