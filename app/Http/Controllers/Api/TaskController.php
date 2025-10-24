<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $r)
    {
        $this->authorize('viewAny', Task::class);

        $filters = $r->only('status','assignee_id','due_before','search','page');
        $key = 'tasks:index:' . md5(json_encode($filters));

        $data = Cache::remember($key, 600, function() use ($r) {
            $q = Task::with(['author:id,name','assignee:id,name'])->latest();
            if ($r->filled('status'))      $q->where('status', $r->status);
            if ($r->filled('assignee_id')) $q->where('assignee_id', $r->assignee_id);
            if ($r->filled('due_before'))  $q->whereDate('due_date','<=',$r->due_before);
            if ($r->filled('search'))      $q->where('title','like','%'.$r->search.'%');
            return $q->paginate(10);
        });

        return response()->json($data);
    }

    public function store(TaskStoreRequest $r)
    {
        $this->authorize('create', Task::class);
        \Log::info('Author in store', \App\Models\User::find($r->user()->id)->toArray());
        $task = Task::create([
            'author_id'   => $r->user()->id,
            'assignee_id' => $r->validated('assignee_id') ?? null,
            'title'       => $r->validated('title'),
            'description' => $r->validated('description'),
            'status'      => $r->validated('status') ?? 'pending',
            'due_date'    => $r->validated('due_date'),
        ]);

        $this->flushTaskListCache();

        return response()->json($task, 201);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        return $task->load(['author:id,name','assignee:id,name']);
    }

    public function update(TaskUpdateRequest $r, Task $task)
    {
        $this->authorize('update', $task);
        $task->update($r->validated());
        $this->flushTaskListCache();
        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();
        $this->flushTaskListCache();
        return response()->noContent();
    }

    public function assignedToMe(Request $r)
    {
        return Task::with(['author:id,name','assignee:id,name'])
            ->where('assignee_id', $r->user()->id)
            ->latest()->paginate(10);
    }

    private function flushTaskListCache(): void
    {
        // FILE cache has no tags; simplest strategy in dev:
        // nuke everything related to task lists by iterating known pages/filters in production,
        // or, if you use Redis, prefer tags (shown below).
        Cache::flush(); // acceptable for dev; for prod use tags or a key registry.
    }
}
