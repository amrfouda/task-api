<?php

namespace App\Jobs;

use App\Models\Comment;
use App\Notifications\NewCommentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NewCommentOnTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $commentId) {}

    public function handle(): void
    {
        $comment = Comment::with(['task.author', 'user'])->findOrFail($this->commentId);
        $author  = $comment->task->author;
        if ($author) {
            $author->notify(new NewCommentNotification($comment));
        }
    }
}
