<?php namespace App\Services;

use App\Board;
use App\BoardSetting;
use App\SiteSetting;
use App\Option;
use Cache;

class SettingManager {
	
	/**
	 * Cached settings for the entire site.
	 *
	 * @var collection  of SiteSetting
	 */
	protected $settings;
	
	/**
	 * Create a new authentication controller instance.
	 *
	 * @return void
	 */
	public function __construct($app)
	{
		$this->settings = $this->fetchSettings();
	}
	
	/**
	 * Magic method allows invocation of class as shortcut to getSetting.
	 *
	 * @param  string  $option_name
	 * @return mixed
	 */
	public function __invoke($option_name)
	{
		return $this->getSetting($option_name);
	}
	
	/**
	 * Returns the value of a single setting.
	 *
	 * @param  string  $option_name
	 * @return mixed
	 */
	public function get($option_name)
	{
		return $this->getSetting($option_name);
	}
	
	/**
	 * Returns the value of a single setting.
	 *
	 * @param  string  $option_name
	 * @return mixed
	 */
	public function getSetting($option_name)
	{
		foreach ($this->settings as $settings)
		{
			if ($settings->option_name == $option_name)
			{
				return $settings->option_value;
			}
		}
		
		return null;
	}
	
	/**
	 * Returns all settings.
	 *
	 * @return collection
	 */
	public function getSettings()
	{
		return $this->settings;
	}
	
	/**
	 * Loads all settings.
	 *
	 * @return collection
	 */
	public function fetchSettings()
	{
		switch (env('CACHE_DRIVER'))
		{
			case "file" :
			case "database" :
				return SiteSetting::getAll();
			
			// We only cache settings when we are using a memory cache.
			// Anything else is slower than the query.
			default :
				return Cache::remember('site.settings', 30, function()
				{
					return SiteSetting::getAll();
				});
		}
	}
};