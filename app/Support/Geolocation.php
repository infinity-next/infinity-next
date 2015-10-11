<?php namespace App\Support;

use Request;

class Geolocation {
	
	/**
	 * The IP address we're locating.
	 *
	 * @var string  IPv4 or IPv6 Address
	 */
	protected $ip;
	
	/**
	 * If our IP is the request IP.
	 *
	 * @var boolean
	 */
	protected $current;
	
	/**
	 * Builds a Geolocation interpreter instance.
	 *
	 * @param  string|null
	 * @return Geolocation
	 */
	public function __cosntruct($ip = null)
	{
		if (is_null($ip))
		{
			$ip = Request::ip();
		}
		
		$this->ip = $ip;
		$this->current = ($ip == Request::ip());
		
		return $this;
	}
	
	/**
	 * Returns the country code when you echo the object.
	 *
	 * @return string  (2 character country code)
	 */
	public function __toString()
	{
		return $this->getCountryCode();
	}
	
	/**
	 * Returns the ISO 3166-1 alpha-2 country codes for the IP.
	 *
	 * @return string  (2 character country code)
	 */
	public function getCountryCode()
	{
		$cc = "";
		
		// This checks for the CloudFlare country code provided for any service hidden behind Cloudflare.
		// It's probably the easiest, fastest, and most reliable way to achieve this.
		if (isset($_SERVER['HTTP_CF_IPCOUNTRY']))
		{
			return strtolower($_SERVER["HTTP_CF_IPCOUNTRY"]);
		}
		
		return $cc;
	}
	
}
