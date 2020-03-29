<?php

namespace App\Support;

use App\Contracts\Permittable;
use App\Support\IP\CIDR as CIDR;
use Log;
use Request;

class IP extends CIDR
{
    public function __construct($cidr = null, $end = null)
    {
        if ($cidr === null || $cidr === '') {
            return parent::__construct(Request::ip());
        }

        // Passing a static, return it.
        if ($cidr instanceof static) {
            // If no end, return $cidr.
            if ($end === null) {
                return parent::__construct($cidr->getStart(), $cidr->getEnd());
            }

            // If end is a static, we are creating a range with two exising IPs.
            if ($end instanceof static) {
                $end = $end->getEnd();
            }
            // If $end is binary, return it to base64.
            if (is_binary($end)) {
                $end = inet_ntop($end);
            }

            return parent::__construct($cidr->getStart(), $end);
        } elseif (is_binary($cidr)) {
            try {
                $start = inet_ntop($cidr);
            } catch (\Exception $e) {
                Log::warning("App\Support\IP::__construct trying to make IP from \$cidr binary value \"{$cidr}\" 0x".bin2hex($cidr).", but it's not a real IP!");

                if (!env('APP_DEBUG', false)) {
                    $start = '127.0.0.1';
                } else {
                    throw $e;
                }
            }
        } else {
            try {
                $start = inet_ntop($cidr);
            } catch (\Exception $e) {
                $start = $cidr;
            }
            if ($start === false) {
                $start = $cidr;
            }
        }

        // Passing a static, return it.
        if ($end instanceof static) {
            return parent::__construct($start, $end->getEnd());
        } elseif (is_binary($end)) {
            try {
                $end = inet_ntop($end);
            } catch (\Exception $e) {
                Log::warning("App\Support\IP::__construct trying to make IP from \$end binary value \"{$cidr}\" 0x".bin2hex($end).", but it's not a real IP!");

                if (!env('APP_DEBUG', false)) {
                    $end = '127.0.0.1';
                } else {
                    throw $e;
                }
            }
        }

        return parent::__construct($start, $end);
    }

    public function __toString()
    {
        return $this->toText();
    }

    public function getStartForSQL()
    {
        return $this->toSQL(true);
    }

    public function geEndForSQL()
    {
        return $this->toSQL(false);
    }

    public function toBinary()
    {
        return inet_pton($this->getStart());
    }

    public function toSQL($start = true)
    {
        $ip = $start ? $this->getStart() : $this->getEnd();

        return binary_sql(inet_pton($ip));
    }

    public function toLong()
    {
        return sprintf('%u', ip2long(parent::__toString()));
    }

    public function toText()
    {
        return parent::__toString();
    }

    public function toTextForUser(?Permittable $user = null)
    {
        if (is_null($user)) {
            $user = user();
        }

        return $user->getTextForIP($this->toText());
    }

    public function intersects($cidr)
    {
        return self::cidr_intersect(parent::__toString(), $cidr);
    }

     /**
      * Quickly determines if the supplied CIDR is the same as this CIDR.
      *
      * @return bool
      */
     public function is($ip)
     {
         if ($ip instanceof static) {
             return $ip->getStart() === $this->start && $ip->getEnd() === $this->end;
         } elseif (is_string($ip)) {
             return $this->start === $ip && $this->end === $ip;
         }

         return false;
     }

    /**
     * Converts an IPv4 or IPv6 CIDR block into its range.
     *
     * @static
     *
     * @param string   $cidr CIDR block or IP address string.
     * @param int|null $bits If /bits is not specified on string they can be passed via this parameter instead.cidr_intersect
     *
     * @return array A 2 element array with the low, high range
     */
    public static function cidr_to_range($cidr, $bits = null)
    {
        if ($cidr instanceof static) {
            return parent::cidr_to_range($cidr->getCidr(), $bits);
        }

        return parent::cidr_to_range($cidr, $bits);
    }
}
