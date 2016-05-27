<?php

namespace App\Listeners;

use Cache;

class BoardListRecache extends Listener
{
    /**
     * Handle the event.
     *
     * @param Event $event
     */
    public function handle($event)
    {
        Cache::forget('site.boardlist');
    }
}
