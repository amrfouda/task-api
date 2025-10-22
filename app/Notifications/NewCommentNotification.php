<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Comment $comment) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $task = $this->comment->task;
        $who  = $this->comment->user?->name ?? 'Someone';

        return (new MailMessage)
            ->subject("New comment on your task: {$task->title}")
            ->greeting("Hi {$notifiable->name},")
            ->line("{$who} commented:")
            ->line($this->comment->body)
            ->action('View Task', url("/tasks/{$task->id}"))
            ->line('This email was sent asynchronously via queue.');
    }
}
