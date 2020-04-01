<?php

namespace App\Observers;

use App\Report;
use Event;

/**
 * Report model observer..
 *
 * @category   Observers
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class ReportObserver
{
    /**
     * Handles model after create (non-existant save).
     *
     * @param \App\Report $report
     *
     * @return bool
     */
    public function created(Report $report)
    {
        Event::dispatch(new \App\Events\ReportWasCreated($report));

        return true;
    }

    /**
     * Checks if this model is allowed to create (non-existant save).
     *
     * @param \App\Report $report
     *
     * @return bool
     */
    public function creating(Report $report)
    {
        return true;
    }

    /**
     * Handles model after delete (pre-existing hard or soft deletion).
     *
     * @param \App\Report $report
     *
     * @return bool
     */
    public function deleted($report)
    {
        Event::dispatch(new \App\Events\ReportWasDeleted($report));

        return true;
    }

    /**
     * Checks if this model is allowed to delete (pre-existing deletion).
     *
     * @param \App\Report $report
     *
     * @return bool
     */
    public function deleting($report)
    {
        return true;
    }

    /**
     * Handles model after save (pre-existing or non-existant save).
     *
     * @param \App\Report $report
     *
     * @return bool
     */
    public function saved(Report $report)
    {
        return true;
    }

    /**
     * Checks if this model is allowed to save (pre-existing or non-existant save).
     *
     * @param \App\Report $report
     *
     * @return bool
     */
    public function saving(Report $report)
    {
        return true;
    }

    /**
     * Handles model after update (pre-existing save).
     *
     * @param \App\Report $report
     *
     * @return bool
     */
    public function updated(Report $report)
    {
        Event::dispatch(new \App\Events\ReportWasModified($report));

        return true;
    }

    /**
     * Checks if this model is allowed to update (pre-existing save).
     *
     * @param \App\Report $report
     *
     * @return bool
     */
    public function updating(Report $report)
    {
        return true;
    }
}
