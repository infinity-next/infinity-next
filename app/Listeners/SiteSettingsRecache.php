<?php

namespace App\Listeners;

use Cache;

class SiteSettingsRecache extends Listener
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
        Cache::forget('site.settings');
    }
}
