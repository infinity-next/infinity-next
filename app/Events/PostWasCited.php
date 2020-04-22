<?php

namespace App\Events;

use App\Post;
use Illuminate\Queue\SerializesModels;

class PostWasCited extends Event
{
    use SerializesModels;

    /**
     * The post the event is being fired on.
     *
     * @var \App\Post
     */
    public $post;

    /**
     * The post that triggered the event.
     *
     * @var \App\Post
     */
    public $citing;

    /**
     * Create a new event instance.
     */
    public function __construct(Post $post, Post $citing)
    {
        $this->post = $post;
        $this->citing = $citing;
    }
}
