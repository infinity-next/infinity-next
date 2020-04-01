<?php

namespace App\Events;

use App\Post;
use App\User;
use Illuminate\Queue\SerializesModels;

class PostWasCapcoded extends Event
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
    public function __construct(Post $post, User $user)
    {
        $this->action = "post.capcode";
        $this->actionDetails = [
            'board_id' => $post->board_id,
            'board_uri' => $post->board_uri,
            'capcode' => $post->capcode->getCapcodeName(),
            'role' => $post->capcode->role,
        ];

        $this->post = $post;
        $this->user = $user;
    }
}
