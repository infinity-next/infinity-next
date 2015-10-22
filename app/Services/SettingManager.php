<?php namespace App\Services;

use App\Board;
use App\BoardSetting;
use App\SiteSetting;
use App\Option;

use App\Services\UserManager;
use Cache;

class SettingManager {
	
	/**
	 * Public settings.
	 * THESE WILL BE EXPOSED TO THE FRONT-END WITH EVERY REQUEST.
	 *
	 * @var array  of SiteSetting names
	 */
	protected static $whitelist = [
		'attachmentFilesize',
		'postFloodTime',
	];
	
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
	 * @param  mixed  $fallback  Option. Defaults to null.
	 * @return mixed
	 */
	public function get($option_name, $fallback = null)
	{
		$setting = $this->getSetting($option_name);
		
		if (is_null($setting))
		{
			return $fallback;
		}
		
		return $setting;
	}
	
	/**
	 * Returns all settings in an array.
	 *
	 * @return array
	 */
	public function getArray()
	{
		return $this->getSettings()->toArray();
	}
	
	/**
	 * Returns all settings in a json array string for front-end injection.
	 *
	 * @return string  (json array)
	 */
	public function getJson()
	{
		$settings = [];
		
		foreach (static::$whitelist as $setting)
		{
			$settings[$setting] = $this->get($setting);
		}
		
		return json_encode($settings);
	}
	
	/**
	 * Returns the primary navigation array.
	 *
	 * @return array  of [key => url]
	 */
	public function getNavigationPrimary()
	{
		$nav = [
			'home'      => url('/'),
			'boards'    => url('boards.html'),
			'new_board' => url('overboard.html'),
			'panel'     => url('cp'),
		];
		
		
		global $app;
		$manager = $app->make(UserManager::class);
		
		if ($manager->user && $manager->user->canCreateBoard())
		{
			$nav['recent_posts'] = url("cp/boards/create");
		}
		
		
		if (env('CONTRIB_ENABLED', false))
		{
			$nav['contribute'] = url("contribute");
			$nav['donate']     = secure_url("cp/donate");
		}
		
		
		if ($this->get('adventureEnabled'))
		{
			$nav['adventure'] = url("cp/adventure");
		}
		
		
		return $nav;
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