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
		'threadFloodTime',
	];
	
	/**
	 * Cached settings for the entire site.
	 *
	 * @var collection  of SiteSetting
	 */
	protected $settings;
	
	/**
	 * Remembers if we have a stable database connection.
	 *
	 * @var boolean
	 */
	protected $db;
	
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
			'home'         => url('/'),
			'boards'       => url('boards.html'),
			'recent_posts' => url('overboard.html'),
			'panel'        => url('cp'),
		];
		
		
		if ($this->hasDB())
		{
			global $app;
			$manager = $app->make(UserManager::class);
			
			if ($manager->user && $manager->user->canCreateBoard())
			{
				$nav['new_board'] = url("cp/boards/create");
			}
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
	 * Returns the primary navigation board list.
	 *
	 * @return array|false  Returns false if the setting boardListShow is disabled.
	 */
	public function getNavigationPrimaryBoards()
	{
		if ($this->hasDB() && $this->get('boardListShow', false))
		{
			return Cache::remember('site.gnav.boards', 1, function()
			{
				$popularBoardArray = Board::getBoardsForBoardlist(0, 20);
				$popularBoards = collect();
				
				foreach ($popularBoardArray as $popularBoard)
				{
					$popularBoards->push( new Board($popularBoard) );
				}
				
				
				$recentBoards  = Board::where('posts_total', '>', 0)
					->whereNotNull('last_post_at')
					->wherePublic()
					->whereNotIn('board_uri', $popularBoards->pluck('board_uri'))
					->select('board_uri', 'title')
					->orderBy('last_post_at', 'desc')
					->take(20)
					->get();
				
				
				return [
					'popular_boards' => $popularBoards,
					'recent_boards'  => $recentBoards,
				];
			});
		}
		
		return false;
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
	 * Determines if we have a DB connection.
	 * 
	 * @return boolean
	 */
	public function hasDB()
	{
		if (!isset($this->db))
		{
			try
			{
				$this->db = !!\DB::connection()->getDatabaseName();
			}
			catch (\Exception $e)
			{
				$this->db = false;
			}
		}
		
		return $this->db;
	}
	
	/**
	 * Loads all settings.
	 *
	 * @return collection
	 */
	public function fetchSettings()
	{
		if ($this->hasDB())
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
		
		return [];
	}
};