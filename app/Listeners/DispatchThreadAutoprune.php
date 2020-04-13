<?php

namespace App\Listeners;

use App\Post;
use App\Jobs\ThreadAutoprune;

/**
 * Dispatches the thread autopruner after a thread has changed.
 *
 * @category   Job
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2020 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @see \Illuminate\Auth\Middleware\Authenticate
 *
 * @since      0.6.0
 */
class DispatchThreadAutoprune extends Listener
{
    /**
     * Handle the event.
     *
     * @param Event $event
     */
    public function handle($event)
    {
        if (isset($event->post) && $event->post instanceof Post) {
            ThreadAutoprune::dispatch($event->post);
        }
    }
}
