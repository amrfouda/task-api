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
        $authorToken = $this->postJson('/api/login',['email'=>$author->email,'password'=>'password'])->json('token');
        $assigneeToken = $this->postJson('/api/login',['email'=>$assignee->email,'password'=>'password'])->json('token');

        $taskId = $this->withHeader('Authorization', "Bearer $authorToken")
            ->postJson('/api/tasks', ['title'=>'Policy Test'])
            ->json('id');

        $this->withHeader('Authorization', "Bearer $authorToken")
            ->postJson("/api/tasks/{$taskId}/assign", ['assignee_id'=>$assignee->id])
            ->assertOk();

        // Assignee can view
        $this->withHeader('Authorization', "Bearer $assigneeToken")
            ->getJson("/api/tasks/{$taskId}")
            ->assertOk();

        // Assignee cannot update/delete
        $this->withHeader('Authorization', "Bearer $assigneeToken")
            ->putJson("/api/tasks/{$taskId}", ['title'=>'Nope'])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $assigneeToken")
            ->deleteJson("/api/tasks/{$taskId}")->assertStatus(403);
    }
}