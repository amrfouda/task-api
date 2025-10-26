<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_filters_status_assignee_due_before_search_and_pagination(): void
    {
        $author = User::factory()->create();
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();

        Sanctum::actingAs($author);

        // seed tasks
        Task::factory()->create(['author_id'=>$author->id, 'title'=>'Alpha note', 'status'=>'pending',  'assignee_id'=>$u1->id, 'due_date'=>Carbon::today()->addDays(3)]);
        Task::factory()->create(['author_id'=>$author->id, 'title'=>'Beta something', 'status'=>'completed', 'assignee_id'=>$u2->id, 'due_date'=>Carbon::today()->addDays(10)]);
        Task::factory()->create(['author_id'=>$author->id, 'title'=>'Gamma alpha', 'status'=>'pending', 'assignee_id'=>null, 'due_date'=>Carbon::today()->addDays(1)]);

        // status
        $this->getJson('/api/tasks?status=pending')->assertOk()
            ->assertJsonFragment(['status'=>'pending']);

        // assignee
        $this->getJson("/api/tasks?assignee_id={$u1->id}")->assertOk()
            ->assertJsonFragment(['assignee_id'=>$u1->id]);

        // due_before
        $date = Carbon::today()->addDays(2)->toDateString();
        $this->getJson("/api/tasks?due_before={$date}")->assertOk();

        // search
        $this->getJson('/api/tasks?search=alpha')->assertOk()
            ->assertJsonFragment(['title'=>'Alpha note']);

        // pagination shape
        $this->getJson('/api/tasks?page=1')->assertOk()
            ->assertJsonStructure(['current_page','data','per_page','total']);
    }
}