<?php

namespace App\Listeners;

use App\RoleCache;

class UserRecachePermissions extends Listener
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
        if (isset($event->user)) {
            $event->user->forgetPermissions();
        } elseif (isset($event->users)) {
            foreach ($event->users as $user) {
                $user->forgetPermissions();
            }
        } else {
            RoleCache::delete();
        }
    }
}
