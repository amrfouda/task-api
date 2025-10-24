<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_task_should_invalidate_tasks_cache()
    {
        $author = User::factory()->create(['password' => bcrypt('secret')]);
        $token  = $this->postJson('/api/login', [
            'email' => $author->email,
            'password' => 'secret',
        ])->json('token');

        // Create via controller (store calls flush after create)
        $create = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/tasks', ['title' => 'Old'])
            ->assertCreated();

        $taskId = $create->json('id');

        // Spy on Cache and then hit the update endpoint (which calls flush)
        Cache::spy();

        $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/tasks/{$taskId}", ['title' => 'New'])
            ->assertOk();

        Cache::shouldHaveReceived('flush')->once();
    }
}