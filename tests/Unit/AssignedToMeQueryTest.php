<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignedToMeQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_query_returns_only_tasks_assigned_to_user()
    {
        $me     = User::factory()->create();
        $other  = User::factory()->create();
        $author = User::factory()->create();

        $mine   = Task::factory()->create(['author_id' => $author->id, 'assignee_id' => $me->id]);
        $notMe1 = Task::factory()->create(['author_id' => $author->id, 'assignee_id' => $other->id]);
        $notMe2 = Task::factory()->create(['author_id' => $author->id, 'assignee_id' => null]);

        $ids = Task::where('assignee_id', $me->id)->orderByDesc('id')->pluck('id')->all();

        $this->assertContains($mine->id, $ids);
        $this->assertNotContains($notMe1->id, $ids);
        $this->assertNotContains($notMe2->id, $ids);
    }
}