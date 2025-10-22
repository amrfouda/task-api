<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskAssignmentController extends Controller
{
    use AuthorizesRequests;
    public function assign(Request $r, Task $task)
    {
        $this->authorize('assign', $task);
        $data = $r->validate(['assignee_id' => 'required|exists:users,id']);
        $task->update(['assignee_id' => $data['assignee_id']]);
        Cache::flush();
        return response()->json($task);
    }

    public function unassign(Task $task)
    {
        $this->authorize('unassign', $task);
        $task->update(['assignee_id' => null]);
        Cache::flush();
        return response()->json($task);
    }
}
