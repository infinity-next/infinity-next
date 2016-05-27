<?php

namespace App\Listeners;

use App\Post;

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
            $event->post->clearPostHTMLCache();
        }
    }
}
