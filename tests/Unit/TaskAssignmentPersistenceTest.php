<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskAssignmentPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_assign_persists_assignee_id()
    {
        $author   = User::factory()->create();
        $assignee = User::factory()->create();

        $task = Task::factory()->create([
            'author_id'   => $author->id,
            'assignee_id' => null,
        ]);

        // simulate your assign logic
        $task->assignee_id = $assignee->id;
        $task->save();

        $this->assertDatabaseHas('tasks', [
            'id'          => $task->id,
            'assignee_id' => $assignee->id,
        ]);
    }
}