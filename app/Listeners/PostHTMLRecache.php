<?php

namespace App\Listeners;

use App\Post;
use Cache;

class PostHTMLRecache extends Listener
{
    /**
     * Handle the event.
     *
     * @param Event $event
     */
    public function handle($event)
    {
        if ($event->post instanceof Post) {
            $event->post->forget();
        }
        elseif (is_array($event->post) && isset($event->post['post_id'])) {
            Post::find($event->post['post_id'])->forget();
        }
    }
}
