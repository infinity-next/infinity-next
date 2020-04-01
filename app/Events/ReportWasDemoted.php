<?php

namespace App\Events;

use App\Report;
use Illuminate\Queue\SerializesModels;

/**
 * @category   Events
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class ReportWasDemoted extends Event
{
    use SerializesModels;

    /**
     * Model the event is being fired on.
     *
     * @var \App\Report
     */
    public $report;

    /**
     * Create a new event instance.
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }
}
