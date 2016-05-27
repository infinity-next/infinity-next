<?php

namespace App\Listeners;

use App\Board;
use App\Post;

class BoardRecachePages extends Listener
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
        if (isset($event->board) && $event->board instanceof Board) {
            $board = $event->board;
        } elseif (isset($event->post) && $event->post instanceof Post) {
            $board = $event->post->board;
        }

        if (isset($board)) {
            $board->clearCachedPages();
        }
    }
}
