<?php

namespace App\Events;

use App\Post;
use App\Jobs\ThreadAutoprune;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class PostWasModified implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * The post the event is being fired on.
     *
     * @var \App\Post
     */
    public $post;

    /**
     * Create a new event instance.
     */
    public function __construct(Post $post)
    {
        $this->post = $post->toArray();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel("Thread.{$this->post['reply_to']}");
    }
}
