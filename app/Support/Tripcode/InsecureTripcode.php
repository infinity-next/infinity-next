<?php

namespace App\Support\Tripcode;

use App\Contracts\Support\Tripcode;
use Illuminate\Contracts\Support\Htmlable;

/**
 * 2channel tripcodes.
 *
 * @category   Tripcode
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2020 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class InsecureTripcode implements Htmlable, Tripcode
{
    protected $password;
    protected $tripcode;

    /**
     * Returns a tripcode from a password.
     * Note that this is a public, easily breakable algorithm, and is therefore insecure.
     * However, it is retained because of its heavy use on anonymous websites from 2ch to 4chan.
     *
     * @param  string  $password
     *
     * @return string (Tripcode)
     */
    public function __construct($password)
    {
        $tripcode = mb_convert_encoding($password, 'Shift_JIS', 'UTF-8');
        $salt = substr($tripcode.'H..', 1, 2);
        $salt = preg_replace('/[^.-z]/', '.', $salt);
        $salt = strtr($salt, ':;<=>?@[\]^_`', 'ABCDEFGabcdef');
        $tripcode = substr(crypt($tripcode, $salt), -10);

        $this->password = $password;
        $this->tripcode = $tripcode;

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
