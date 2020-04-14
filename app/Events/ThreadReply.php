<?php

namespace App\Events;

use App\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ThreadReply implements ShouldBroadcast
{
    use SerializesModels;

    public $thread;
    public $reply;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Post $thread, Post $reply)
    {
        $thread->setAppendHtml(true);
        $this->thread = $thread->toArray();

        $reply->setAppendHtml(true);
        $this->reply = $reply->toArray();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel("Thread.{$this->thread['post_id']}");
    }
}
