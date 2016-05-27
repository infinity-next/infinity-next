<?php

namespace App\Listeners;

use App\Post;

class ReportMarkSuccessful extends Listener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param Event $event
     */
    public function handle($event)
    {
        if (isset($event->post) && $event->post instanceof Post) {
            $reports = $event->post->reports()
                ->where('is_dismissed', false)
                ->where('is_successful', false)
                ->update(['is_successful' => true]);
        }
    }
}
