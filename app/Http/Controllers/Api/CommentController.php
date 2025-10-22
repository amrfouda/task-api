<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentStoreRequest;
use App\Http\Requests\CommentUpdateRequest;
use App\Jobs\NewCommentOnTask;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Support\Facades\Cache;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CommentController extends Controller
{
    use AuthorizesRequests;
    public function index(Task $task)
    {
        $this->authorize('view', $task);

        $cacheKey = "task:{$task->id}:comments";
        $comments = Cache::remember($cacheKey, 600, fn() =>
            $task->comments()->with('user:id,name')->latest()->get()
        );

        return response()->json($comments);
    }

    public function store(CommentStoreRequest $r, Task $task)
    {
        $this->authorize('create', [Comment::class, $task]);

        $comment = $task->comments()->create([
            'user_id' => $r->user()->id,
            'body'    => $r->validated('body'),
        ]);

        Cache::forget("task:{$task->id}:comments");

        NewCommentOnTask::dispatch($comment->id);

        return response()->json($comment, 201);
    }

    public function update(CommentUpdateRequest $r, Comment $comment)
    {
        $this->authorize('update', $comment);
        $comment->update(['body' => $r->validated('body')]);
        Cache::forget("task:{$comment->task_id}:comments");
        return response()->json($comment);
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        $taskId = $comment->task_id;
        $comment->delete();
        Cache::forget("task:{$taskId}:comments");
        return response()->noContent();
    }
}
