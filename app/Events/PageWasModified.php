<?php

namespace App\Events;

use App\Page;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;

/**
 * Page observational event.
 *
 * @package    InfinityNext
 * @category   Events
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 * @since      0.6.0
 */
class PageWasModified extends Event
{
    use SerializesModels;

    /**
     * The page the event is being fired on.
     *
     * @var \App\Page
     */
    public $page;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Page $page)
    {
        $this->page = $page;
    }
}
