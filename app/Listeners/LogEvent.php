<?php

namespace App\Listeners;

use App\Board;
use App\Post;
use App\Log;
use App\User;
use App\Support\IP;

class LogEvent extends Listener
{
    /**
     * Logs an action.
     *
     * @param Event $event
     */
    public function handle($event)
    {
        $log = new Log;
        $log->action_name = $event->action;
        $log->user_ip = new IP;

        if ($event->board instanceof Board) {
            $log->board_uri = $event->board->board_uri;
        }

        if ($event->user instanceof User) {
            $log->user_id = $event->user->user_id;
        }

        $actionDetails = $event->actionDetails;
        if (!is_null($actionDetails) && !is_array($actionDetails)) {
            $actionDetails = [ $event->actionDetails ];
        }
        if (!is_null($actionDetails)) {
            $actionDetails = json_encode($actionDetails);
        }
        $log->action_details = $actionDetails;

        $log->save();
    }
}
