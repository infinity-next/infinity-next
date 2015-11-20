<?php namespace App\Support;

use App\Contracts\PermissionUser;
use App\Support\IP\CIDR as CIDR;
use Log;
use Request;

class IP extends CIDR {
	
	public function __construct($cidr = null, $end = null)
	{
		if ($cidr === null)
		{
			return parent::__construct(Request::ip());
		}
		else if(is_resource($cidr))
		{
			$cidr = stream_get_contents($cidr);
		}
		
		if (is_resource($end))
		{
			$end = stream_get_contents($end);
		}
		
		
		// Passing a static, return it.
		if ($cidr instanceof static)
		{
			// If no end, return $cidr.
			if ($end === null)
			{
				return parent::__construct($cidr->getStart(), $cidr->getEnd());
			}
			
			// If end is a static, we are creating a range with two exising IPs.
			if ($end instanceof static)
			{
				$end = $end->getEnd();
			}
			// If $end is binary, return it to base64.
			if (is_binary($end))
			{
				$end = inet_ntop($end);
			}
			
			return parent::__construct($cidr->getStart(), $end);
		}
		else if (is_binary($cidr))
		{
			try
			{
				$start = inet_ntop($cidr);
			}
			catch (\Exception $e)
			{
				Log::warning("App\Support\IP::__construct trying to make IP from \$cidr binary value \"{$cidr}\" 0x" . devbin($start) . ", but it's not a real IP!");
				
				if (!env('APP_DEBUG', false))
				{
					$start = "127.0.0.1";
				}
				else
				{
					throw $e;
				}
			}
		}
		else
		{
			try
			{
				$start = inet_ntop(binary_unsql($cidr));
			}
			catch (\Exception $e)
			{
				$start = $cidr;
			}
		}
		
		// Passing a static, return it.
		if ($end instanceof static)
		{
			return parent::__construct($start, $end->getEnd());
		}
		else if (is_binary($end))
		{
			try
			{
				$end = inet_ntop($end);
			}
			catch (\Exception $e)
			{
				Log::warning("App\Support\IP::__construct trying to make IP from \$end binary value \"{$cidr}\" 0x" . devbin($end) . ", but it's not a real IP!");
				
				if (!env('APP_DEBUG', false))
				{
					$end = "127.0.0.1";
				}
				else
				{
					throw $e;
				}
			}
		}
		
		return parent::__construct($start, $end);
	}
	
	public function __toString()
	{
		return $this->toSQL();
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
		$ip = $start ? $this->getStart() : $this->getEnd() ;
		
		return binary_sql(inet_pton($ip));
	}
	
	public function toText()
	{
		return parent::__toString();
	}
	
	public function toTextForUser(PermissionUser $user)
	{
		return $user->getTextForIP($this->toText());
	}
	
	/**
	 * Converts an IPv4 or IPv6 CIDR block into its range.
	 *
	 * @static
	 * @param  string       $cidr CIDR block or IP address string.
	 * @param  integer|null $bits If /bits is not specified on string they can be passed via this parameter instead.cidr_intersect
	 * @return array  A 2 element array with the low, high range
	 */
	public static function cidr_to_range($cidr, $bits = null)
	{
		if ($cidr instanceof static)
		{
		if ($bits < 0)
		{
			dd($cidr->prefix);
		}
			return parent::cidr_to_range($cidr->getCidr(), $bits);
		}
		
		return parent::cidr_to_range($cidr, $bits);
	}
}
