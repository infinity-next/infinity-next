<?php

namespace App\Listeners;

use App\Post;


class ThreadRecache extends Listener
{
    /**
     * Handle the event.
     *
     * @param Event $event
     */
    public function handle($event)
    {
        if ($event->post instanceof Post) {
            $event->post->clearThreadCache();
        }
    }
}
