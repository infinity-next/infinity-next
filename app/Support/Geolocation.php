<?php

namespace App\Support;

use App;
use App\Contracts\PermissionUser;
use Artisan;
use Cache;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\InvalidDatabaseException;
use Request;
use Storage;

class Geolocation
{
    /**
     * The IP address we're locating.
     *
     * @var string IPv4 or IPv6 Address
     */
    protected $ip;

    /**
     * If our IP is the request IP.
     *
     * @var bool
     */
    protected $current;

    /**
     * Builds a Geolocation interpreter instance.
     *
     * @param  string|null
     *
     * @return Geolocation
     */
    public function __cosntruct($ip = null)
    {
        if (is_null($ip)) {
            $ip = Request::ip();
        }

        $this->ip = $ip;
        $this->current = ($ip == Request::ip());

        return $this;
    }

    /**
     * Returns the country code when you echo the object.
     *
     * @return string (2 character country code)
     */
    public function __toString()
    {
        return $this->getCountryCode();
    }

    /**
     * Returns the ISO 3166-1 alpha-2 country codes for the IP.
     *
     * @return string  (2 character country code or empty)
     */
    public function getCountryCode()
    {
        $cc = '';

        if (!App::make(PermissionUser::class)->isAccountable()) {
            $cc = 'tor';
        }
        // This checks for the CloudFlare country code provided for any service hidden behind Cloudflare.
        // It's probably the easiest, fastest, and most reliable way to achieve this.
        elseif ($cc = Request::header('HTTP_CF_IPCOUNTRY', false)) {
            $cc = strtolower($cc);

            if ($cc == 't1') {
                $cc = 'tor';
            }
        }
        // Without Cloudflare, check for a Tor address using our own records.
        elseif ($this->isTorExitNode($this->ip)) {
            $cc = 'tor';
        }
        // Not Tor, try a MaxMind lookup.
        elseif ($cc = $this->getCountryCodeWithMaxMind()) {
            // Nothing else to be done.
        }

        return $cc;
    }

    /**
     * Returns the ISO country code using our local MaxMind DB.
     *
     * @return string
     */
    public function getCountryCodeWithMaxMind()
    {
        // Try a lookup to MaxMind.
        try {
            $reader = new Reader(Storage::get('GeoLite2-Country.mmdb'));
            $record = $reader->country($this->ip);

            return strtolower($record->country->isoCode);
        }
        // Thrown if our DB doesn't exist.
        catch (InvalidDatabaseException $e) {
            Log::critical($e);
        }
        // Thrown if address not in DB.
        catch (AddressNotFoundException $e) {
            // Nothing. This is safe to ignore.
        }

        return '';
    }

    /**
     * Determines if our connection is from a Tor IP address.
     *
     * @static
     *
     * @param string $ip IP address.
     *
     * @return bool
     */
    public static function isTorExitNode($ip)
    {
        if (!Cache::has('tor-exit-nodes')) {
            Arisan::call('tor:pull');
        }

        $ips = Cache::get('tor-exit-nodes');

        return $ips->search($ip) !== false;
    }
}
