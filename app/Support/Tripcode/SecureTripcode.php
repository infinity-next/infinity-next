<?php

namespace App\Support\Tripcode;

use App\Contracts\Support\Tripcode;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Secure tripcodes.
 *
 * @category   Tripcode
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2020 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class SecureTripcode implements Htmlable, Tripcode
{
    protected $password;
    protected $tripcode;

    /**
     * Returns a salted tripcode from a password.
     *
     * @param string $trip
     *
     * @return string (Tripcode)
     */
    public function __construct($password)
    {
        $this->password = $password;
        $this->tripcode = hash('tiger128,3', config('app.key') . " {$password} RPT {$password} THE WORLD WONDERS");

        return $this;
    }

    public function __toString()
    {
        return $this->tripcode;
    }

    public function toHtml()
    {
        return $this->tripcode;
    }
}
