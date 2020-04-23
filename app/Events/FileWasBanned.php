<?php

namespace App\Events;

use App\Post;
use App\FileStorage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class FileWasBanned implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * The model the event has fired on.
     *
     * @var \App\File
     */
    public $file;
    public $post;

    /**
     * Create a new event instance.
     */
    public function __construct(Post $post, FileStorage $file)
    {
        $this->file = $file;
        $this->post = $post;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        if ($this->post['reply_to']) {
            return new PresenceChannel("Thread.{$this->thread['reply_to']}");
        }
        else {
            return new PresenceChannel("Thread.{$this->thread['post_id']}");
        }
    }
}
