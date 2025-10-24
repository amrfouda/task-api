<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignee_can_view_but_cannot_update_or_delete(): void
    {
        $author   = User::factory()->create();
        $assignee = User::factory()->create();

        // 1. Create the task without assignee (via API, acting as the Author)
        $taskResponse = $this->actingAs($author)
            ->postJson('/api/tasks', ['title' => 'Policy Test'])
            ->assertCreated();

        $taskId = $taskResponse->json('id');

        // 2. Assign the task to the assignee (via API, acting as the Author)
        $this->actingAs($author)
            ->postJson("/api/tasks/{$taskId}/assign", ['assignee_id' => $assignee->id])
            ->assertOk();

        // Fetch the task again to ensure the model used in the policy check is fresh
        // (This is a good practice, though route model binding should handle it)
        $task = \App\Models\Task::find($taskId);

        // Sanity check to ensure users are distinct and assignment is correct
        $this->assertNotEquals($author->id, $assignee->id, 'Author and Assignee must be distinct users.');
        $this->assertEquals($assignee->id, $task->assignee_id, 'Task must be assigned to the assignee.');

        // Assignee can view
        $this->actingAs($assignee)
            ->getJson("/api/tasks/{$taskId}")
            ->assertOk();

        // Assignee cannot update/delete (Expected 403)
        $this->actingAs($assignee)
            ->putJson("/api/tasks/{$taskId}", ['title' => 'Nope'])
            ->assertStatus(403); // This should now correctly assert 403

        $this->actingAs($assignee)
            ->deleteJson("/api/tasks/{$taskId}")
            ->assertStatus(403);
    }
}