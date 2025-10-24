<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CacheSanityTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_list_reflects_updates_after_mutation(): void
    {
        $author = User::factory()->create();
        $token = $this->postJson('/api/login',['email'=>$author->email,'password'=>'password'])->json('token');

        $taskId = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/tasks', ['title'=>'CacheTitle'])
            ->json('id');

        // warm "cache"
        $this->withHeader('Authorization', "Bearer $token")->getJson('/api/tasks')->assertOk();

        // update task title
        $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/tasks/{$taskId}", ['title'=>'NewTitle'])
            ->assertOk();

        // list should show NewTitle (i.e., invalidation worked)
        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonFragment(['title'=>'NewTitle']);
    }
}