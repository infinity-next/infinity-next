<?php

namespace App\Observers;

use App\Board;
use Event;

/**
 * Board observers.
 *
 * @category   Observers
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class BoardObserver
{
    /**
     * Handles model after create (non-existant save).
     *
     * @param \App\Board $board
     *
     * @return bool
     */
    public function created(Board $board)
    {
        Event::dispatch(new \App\Events\BoardWasCreated($board, $board->operator));

        return true;
    }

    /**
     * Checks if this model is allowed to create (non-existant save).
     *
     * @param \App\Board $board
     *
     * @return bool
     */
    public function creating(Board $board)
    {
        return true;
    }

    /**
     * Handles model after delete (pre-existing hard or soft deletion).
     *
     * @param \App\Board $board
     *
     * @return bool
     */
    public function deleted($page)
    {
        Event::dispatch(new \App\Events\BoardWasDeleted($page));

        return true;
    }

    /**
     * Checks if this model is allowed to delete (pre-existing deletion).
     *
     * @param \App\Board $board
     *
     * @return bool
     */
    public function deleting($page)
    {
        return true;
    }

    /**
     * Handles model after save (pre-existing or non-existant save).
     *
     * @param \App\Board $board
     *
     * @return bool
     */
    public function saved(Board $board)
    {
        return true;
    }

    /**
     * Checks if this model is allowed to save (pre-existing or non-existant save).
     *
     * @param \App\Board $board
     *
     * @return bool
     */
    public function saving(Board $board)
    {
        return true;
    }

    /**
     * Handles model after update (pre-existing save).
     *
     * @param \App\Board $board
     *
     * @return bool
     */
    public function updated(Board $board)
    {
        Event::dispatch(new \App\Events\BoardWasModified($board));

        // Trigger a role recache if the board has been reassigned.
        if (!$board->exists && !$$board->wasRecentlyCreated) {
            foreach ($board->getDirty() as $attribute => $value) {
                if ($attribute === 'operated_by') {
                    Event::dispatch(new \App\Events\BoardWasReassigned($board, $board->operator));
                    break;
                }
            }
        }

        return true;
    }

    /**
     * Checks if this model is allowed to update (pre-existing save).
     *
     * @param \App\Board $board
     *
     * @return bool
     */
    public function updating(Board $board)
    {
        return true;
    }
}
