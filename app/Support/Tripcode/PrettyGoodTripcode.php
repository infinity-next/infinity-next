<?php

namespace App\Support\Tripcode;

use App\Contracts\Support\Tripcode;
use App\Support\Tripcode\InvalidPgpTripcode;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Htmlable;
use \Crypt_GPG;
use \Crypt_GPG_NoDataException;
use Storage;

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
    protected $message;
    protected $fingerprint;
    protected $timestamp;
    protected $name;
    protected $email;

    /**
     * Singleton GPG instance.
     *
     * @static
     * @var Crypt_GPG
     */
    protected static $gpg;

    /**
     * Returns a salted tripcode from a password.
     *
     * @param  string  $trip
     * @return string  (Tripcode)
     *
     * @throws App\Support\Tripcode\InvalidPgpTripcode
     */
    public function __construct($data)
    {
        $gpg = static::getGpg();
        $decrypted = $gpg->decryptAndVerify($data);
        $sigs = $decrypted['signatures'];
        $data = $decrypted['data'];

        //array:2 [
        //  "data" => "I love having fun on the Internet!"
        //  "signatures" => array:1 [
        //    0 => Crypt_GPG_Signature {#920
        //      -_id: "nSbSCuARZ/ihZTdJAv3lfxvmETQ"
        //      -_keyFingerprint: "36E37BF96C8574F110F1B60F9FE5996EA30CD54D"
        //      -_keyId: "9FE5996EA30CD54D"
        //      -_creationDate: 1588077038
        //      -_expirationDate: 0
        //      -_userId: Crypt_GPG_UserId {#936
        //        -_name: "Joshua Moon"
        //        -_comment: ""
        //        -_email: "josh@9chan.org"
        //        -_isRevoked: false
        //        -_isValid: true
        //      }
        //      -_isValid: true
        //    }
        //  ]
        //]

        if (is_array($sigs)) {
            if (count($sigs) > 1) {
                throw new \InvalidArgumentException("Currently cannot verify multiple signatures in one post.");
            }

            $sig = $sigs[0];
            if (!$sig->isValid()) {
                throw new InvalidPgpTripcode;
            }
        }
        else {
            throw new InvalidPgpTripcode;
        }

        $this->fingerprint = $sig->getKeyFingerprint();
        $this->timestamp = $sig->getCreationDate();
        $this->email = $sig->getUserId()->getEmail();
        $this->name = $sig->getUserId()->getName();
        $this->message = $data;

        return $this;
    }

    public function __toString()
    {
        return $this->tripcode;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getName()
    {
        return $this->name;
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

    public static function getGpg()
    {
        if (!static::$gpg) {
            static::$gpg = new Crypt_GPG([
                //'homedir' => Storage::path(''),
                //'publicKeyring' => Storage::path('pubring.gpg'),
                //'debug' => config('app.debug'),
            ]);
        }

        return static::$gpg;
    }

    public static function insertKey(string $pubkey)
    {
        $gpg = static::getGpg();

        return $gpg->importKey($pubkey);
        // array:6 [
        //    "fingerprint" => "8E570D9F7F70256595769E49F9BC5BCAD7E18635"
        //    "fingerprints" => array:1 [
        //        0 => "8E570D9F7F70256595769E49F9BC5BCAD7E18635"
        //    ]
        //    "public_imported" => 0
        //    "public_unchanged" => 1
        //    "private_imported" => 0
        //    "private_unchanged" => 0
        //]
    }

    public static function hasSignedMessage(string $message) : bool
    {
        return mb_ereg_match("-+BEGIN PGP SIGNED MESSAGE-+.*-+BEGIN PGP SIGNATURE-+.*-+END PGP SIGNATURE-+", $message);
    }
}
