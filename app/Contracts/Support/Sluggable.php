<?php

namespace App\Contracts\Support;

/**
 * Class API for slugged URLs.
 *
 * @category   Contract
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
interface Sluggable
{
    /**
     * Get this model's URL slug.
     *
     * @return string
     */
    public function getSlug();
}
