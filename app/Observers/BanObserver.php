<?php

namespace App\Observers;

use App\Ban;
use App\Page;
use App\Events\BanWasCreated;

/**
 * Dispatches events related to bans.
 *
 * @category   Observers
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2020 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class BanObserver
{
    /**
     * Handles model after create (non-existant save).
     *
     * @param  \App\Ban  $ban
     *
     * @return bool
     */
    public function created(Ban $ban)
    {
        event(new BanWasCreated($ban));

        return true;
    }
}
