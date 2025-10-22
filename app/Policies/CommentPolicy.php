<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function viewAny(User $user): bool { return true; }

    public function view(User $user, Comment $comment): bool
    {
        $task = $comment->task;
        return $user->id === $task->author_id || $user->id === $task->assignee_id;
    }

    public function create(User $user, \App\Models\Task $task): bool
    {
        return $user->id === $task->author_id || $user->id === $task->assignee_id;
    }

    public function update(User $user, Comment $comment): bool
    {
        $task = $comment->task;
        return $user->id === $comment->user_id || $user->id === $task->author_id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        $task = $comment->task;
        return $user->id === $comment->user_id || $user->id === $task->author_id;
    }
}
