<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_update_and_delete()
    {
        $author   = User::factory()->create();
        $assignee = User::factory()->create();

        $task = Task::factory()->create([
            'author_id'   => $author->id,
            'assignee_id' => $assignee->id,
        ]);

        $policy = new TaskPolicy;

        $this->assertTrue($policy->update($author, $task));
        $this->assertTrue($policy->delete($author, $task));
    }

    public function test_assignee_can_view_but_cannot_update_or_delete()
    {
        $author   = User::factory()->create();
        $assignee = User::factory()->create();

        $task = Task::factory()->create([
            'author_id'   => $author->id,
            'assignee_id' => $assignee->id,
        ]);

        $policy = new TaskPolicy;

        $this->assertTrue($policy->view($assignee, $task));
        $this->assertFalse($policy->update($assignee, $task));
        $this->assertFalse($policy->delete($assignee, $task));
    }

    public function test_random_user_cannot_view_update_or_delete()
    {
        $author = User::factory()->create();
        $rand   = User::factory()->create();

        $task = Task::factory()->create([
            'author_id'   => $author->id,
            'assignee_id' => null,
        ]);

        $policy = new TaskPolicy;

        $this->assertFalse($policy->view($rand, $task));
        $this->assertFalse($policy->update($rand, $task));
        $this->assertFalse($policy->delete($rand, $task));
    }
}