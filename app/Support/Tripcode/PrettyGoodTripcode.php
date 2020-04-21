<?php

namespace App\Support\Tripcode;

use App\Contracts\Support\Tripcode;
use App\Support\Tripcode\InvalidPgpTripcode;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Htmlable;

/**
 * PGP signing.
 *
 * @category   Tripcode
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2020 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class PrettyGoodTripcode implements Htmlable, Tripcode
{
    protected $clearsign;
    protected $message;
    protected $fingerprint;
    protected $timestamp;

    /**
     * Returns a salted tripcode from a password.
     *
     * @param string $trip
     *
     * @return string (Tripcode)
     */
    public function __construct($clearsign)
    {
        throw new InvalidPgpTripcode;

        ## BROKEN: https://github.com/php-gnupg/php-gnupg/issues/18
        $gpg = gnupg_init();
        $verify = gnupg_verify($gpg, $clearsign, false, $this->message);

        if ($verify === false) {
            throw new InvalidPgpTripcode;
        }

        $this->fingerprint = $verify[0]['fingerprint'];
        $this->timestamp = $verify[0]['timestamp'];

        ## REAL VALIDATION STARTS HERE
        $tmpfname = tempnam("/tmp", "nextPgp");
        file_put_contents($tmpfname, $clearsign);

        exec("gpg --verify {$tmpfname} 2>&1", $output);

        // this doesn't work either
        if (count($output) != 3 || strpos($output[2], "Good signature") === false) {
            throw new InvalidPgpTripcode($output[2]);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->tripcode;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getTime()
    {
        return Carbon::createFromTimestamp($this->timestamp);
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getTripcode()
    {
        return $this->fingerprint;
    }

    public function toHtml()
    {
        return $this->fingerprints;
    }
}
