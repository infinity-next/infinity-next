<?php

namespace App\Contracts\Support;

/**
 * Class API for syntactical markup formatting.
 *
 * @category   Contract
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
interface Formattable
{
    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function getFormatted($skipCache = false);
}
