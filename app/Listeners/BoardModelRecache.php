<?php

namespace App\Listeners;

use App\Board;

class BoardModelRecache extends Listener
{
    /**
     * Handle the event.
     *
     * @param Event $event
     */
    public function handle($event)
    {
        if (isset($event->board) && $event->board instanceof Board) {
            $board = $event->board;
        }

        if (isset($board)) {
            $board->clearCachedModel();
        }
    }
}
