<?php

namespace App\Listeners;

use Cache;

class BoardStyleRecache extends Listener
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
        $board = $event->board;

        Cache::forget("board.{$board->board_uri}.stylesheet");
    }
}
