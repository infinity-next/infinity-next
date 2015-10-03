<?php namespace App;

use App\BoardSetting;
use App\Option;
use App\Role;
use App\User;
use App\UserRole;
use App\Services\ContentFormatter;
use App\Contracts\PermissionUser;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

use DB;
use Cache;
use Collection;

use Event;
use App\Events\BoardWasCreated;
use App\Events\BoardWasReassigned;

class Board extends Model {
	
	/**
	 * The RegEx used to check the validity of a board uri.
	 *
	 * @var string
	 */
	const URI_PATTERN = "^[a-z0-9]{1,30}\b$";
	const URI_PATTERN_INNER = "[a-z0-9]{1,30}";
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'boards';
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */
	protected $primaryKey = 'board_uri';
	
	/**
	 * Denotes our primary key is not an autoincrementing integer.
	 *
	 * @var string
	 */
	public $incrementing = false;
	
	/**
	 * Denotes this instance is the currently "opened" board.
	 *
	 * @var boolean
	 */
	public $applicationSingleton = false;
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['board_uri', 'title', 'description', 'created_by', 'operated_by', 'is_overboard', 'is_worksafe', 'is_indexed'];
	
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['created_at', 'created_by', 'operated_by'];
	
	/**
	 * Attributes which are automatically sent through a Carbon instance on load.
	 *
	 * @var array
	 */
	protected $dates = ['created_at', 'updated_at', 'last_post_at'];
	
	/**
	 * A cache of compiled board settings.
	 *
	 * @var array  of arrays which contain Options with BoardSetting.option_value pivot keys
	 */
	protected static $config = [];
	
	/**
	 * Ties database triggers to the model.
	 *
	 * @return void
	 */
	public static function boot()
	{
		parent::boot();
		
		// Setup event bindings...
		
		// Fire event on board created.
		static::created(function(Board $board) {
			Event::fire(new BoardWasCreated($board, $board->operator));
		});
		
		// Handle board reassignment
		static::saved(function(Board $board) {
			foreach( $board->getDirty() as $attribute => $value)
			{
				if ($attribute === "operated_by")
				{
					Event::fire(new BoardWasReassigned($board, $board->operator));
					break;
				}
			}
		});
		
	}
	
	
	public function assets()
	{
		return $this->hasMany('\App\BoardAsset', 'board_uri');
	}
	
	public function posts()
	{
		return $this->hasMany('\App\Post', 'board_uri');
	}
	
	public function logs()
	{
		return $this->hasMany('\App\Log', 'board_uri');
	}
	
	public function operator()
	{
		return $this->belongsTo('\App\User', 'operated_by', 'user_id');
	}
	
	public function owner()
	{
		return $this->belongsTo('\App\User', 'owned_by', 'user_id');
	}
	
	public function staffAssignments()
	{
		return $this->hasManyThrough('App\UserRole', 'App\Role', 'board_uri', 'user_id');
	}
	
	public function threads()
	{
		return $this->hasMany('\App\Post', 'board_uri');
	}
	
	public function roles()
	{
		return $this->hasMany('\App\Role', 'board_uri');
	}
	
	public function settings()
	{
		return $this->hasMany('\App\BoardSetting', 'board_uri');
	}
	
	
	public function canAttach(PermissionUser $user)
	{
		if ($this->getConfig('postAttachmentsMax', 1) > 0)
		{
			return $user->canAttachNew($this) || $user->canAttachOld($this);
		}
		
		return false;
	}
	
	public function canBan(PermissionUser $user)
	{
		return $user->canBan($this);
	}
	
	public function canDelete(PermissionUser $user)
	{
		return $user->canDeleteLocally($this);
	}
	
	public function canEditConfig(PermissionUser $user)
	{
		return $user->canEditConfig($this);
	}
	
	public function canPostReply(PermissionUser $user)
	{
		return $user->canPostReply($this);
	}
	
	public function canPostThread(PermissionUser $user)
	{
		return $user->canPostThread($this);
	}
	
	public function canPostWithoutCaptcha(PermissionUser $user)
	{
		return $user->canPostWithoutCaptcha($this);
	}
	
	public function canPostInLockedThreads(PermissionUser $user)
	{
		return $user->canPostInLockedThreads($this);
	}
	
	
	public function clearCachedThreads()
	{
		switch (env('CACHE_DRIVER'))
		{
			case "file" :
				break;
			
			case "database" :
				DB::table('cache')
					->where('key', 'like', "%board.{$this->board_uri}.thread.%")
					->delete();
				break;
			
			default :
				Cache::tags("board.{$this->board_uri}", "threads")->flush();
				break;
		}
	}
	
	public function clearCachedPages()
	{
		Cache::forget("board.{$this->board_uri}.catalog");
		
		switch (env('CACHE_DRIVER'))
		{
			case "file" :
				for ($i = 1; $i <= $this->getPageCount(); ++$i)
				{
					Cache::forget("board.{$this->board_uri}.page.{$i}");
				}
				break;
			
			case "database" :
				DB::table('cache')
					->where('key', 'like', "%board.{$this->board_uri}.page.%")
					->delete();
				break;
			
			default :
				Cache::tags("board.{$this->board_uri}.pages")->flush();
				break;
		}
	}
	
	
	/**
	 * Gets the default album art for an audio file.
	 *
	 * @return string  url
	 */
	public function getAudioArtURL()
	{
		return url("/img/assets/audio.gif");
	}
	
	/**
	 * Returns a single board_banner BoardAsset.
	 *
	 * @return BoardAsset
	 */
	public function getBannerRandom()
	{
		$banners = $this->getBanners();
		
		if (count($banners) > 0)
		{
			return $banners->random();
		}
		
		return false;
	}
	
	/**
	 * Returns a URL for a single banner, and will consider defaults.
	 *
	 * @return string
	 */
	public function getBannerURL()
	{
		$banners = $this->getBanners();
		
		if (count($banners) > 0)
		{
			return $banners->random()->getURL();
		}
		else if (!$this->isWorksafe() && !$this->hasStylesheet())
		{
			return "/img/logo_yotsuba.png";
		}
		else
		{
			return "/img/logo.png";
		}
		
		return false;
	}
	
	public static function getBoardListBar()
	{
		return [
			static::where('posts_total', '>', '-1')
				->where('is_indexed', 1)
				->orderBy('posts_total', 'desc')
				->take(20)
				->get()
		];
	}
	
	/**
	 * Returns all board_banner type BoardAsset items.
	 *
	 * @return Collection
	 */
	public function getBanners()
	{
		$this->load('assets', 'assets.storage');
		
		return $this->assets
			->where('asset_type', "board_banner");
	}
	
	/**
	 * Returns assignable castes.
	 *
	 * @return collection
	 */
	public function getCastes()
	{
		return $this->roles()
			->whereLevel(Role::ID_JANITOR)
			->get();
	}
	
	/**
	 * This very important method determines the compiled configuration for a board.
	 * It does this by taking extant board Options and then laying on top Board Settings.
	 * Anything null defaults, anything with a value takes that place.
	 * 
	 * @param  string  $option_name  If null, returns compiled config.
	 * @param  mixed  $fallback  If not null, returns itself if there is nothing defined.
	 * @return mixed
	 */
	public function getConfig($option_name = null, $fallback = null)
	{
		$config =& static::$config[$this->board_uri];
		
		if (!is_array($config))
		{
			$config = [];
			// Available options + defaults
			$options  = Option::where('option_type', "board")->get();
			// Defined settings
			$settings = $this->settings;
			
			foreach ($options as $option)
			{
				$option->option_value = $option->default_value;
				$config[$option->option_name] = $option;
				
				foreach ($settings as $setting)
				{
					if ($setting->option_name === $option->option_name)
					{
						$option->option_value = $setting->option_value;
						break;
					}
				}
			}
		}
		
		if (!is_null($option_name))
		{
			foreach ($config as $setting)
			{
				if ($setting->option_name == $option_name)
				{
					$option_value = $setting->option_value;
					
					if (is_null($option_value) || $option_value === "")
					{
						return $fallback;
					}
					
					return $option_value;
				}
			}
		}
		else
		{
			return $config;
		}
		
		return $fallback;
	}
	
	public function getLocalThread($local_id)
	{
		return $this->threads()
			->op()
			->where('board_id', $local_id)
			->get()
			->first();
	}
	
	public function getLocalReply($local_id)
	{
		return $this->posts()
			->where('board_id', $local_id)
			->get()
			->first();
	}
	
	public function getLogs()
	{
		return $this->logs()
			->with('user')
			->take(100)
			->orderBy('created_at', 'desc')
			->get();
	}
	
	public function getPageCount()
	{
		return Cache::remember("board.{$this->board_uri}.pages", 60, function()
		{
			$visibleThreads = $this->threads()->op()->count();
			$threadsPerPage = (int) $this->getConfig('postsPerPage', 10);
			$pageCount      = ceil( $visibleThreads / $threadsPerPage );
			
			return $pageCount > 0 ? $pageCount : 1;
		});
	}
	
	public function getOwnerRole()
	{
		return $this->roles()
			->where('role', "owner")
			->where('caste', NULL)
			->get()
			->first();
	}
	
	/**
	 * Returns an array of castes currently assigned to ths board under the specified role.
	 *
	 * @param  string  $role  Role group.
	 * @param  int|null  $ignoreID  Optional ID to exclude from results.
	 * @return array
	 */
	public function getRoleCastes($role, $ignoreID = null)
	{
		return $this->roles()->where(function($query) use ($role, $ignoreID)
		{
			$query->where('role', $role);
			
			if (!is_null($ignoreID))
			{
				$query->where('role_id', '!=', $ignoreID);
			}
		});
	}
	
	public function getSidebarContent()
	{
		$ContentFormatter = new ContentFormatter();
		
		return $ContentFormatter->formatSidebar($this->getConfig('boardSidebarText'));
	}
	
	public function getSpoilerUrl()
	{
		return url("/img/assets/spoiler.png");
	}
	
	public function getStaff()
	{
		$staff = [];
		$roles = Role::with('users')
			->where('board_uri', $this->board_uri)
			->get();
		
		foreach ($this->roles as $role)
		{
			foreach ($role->users as $user)
			{
				$staff[$user->user_id] = $user;
			}
		}
		
		return $staff;
	}
	
	public function getURL()
	{
		return url($this->board_uri);
	}
	
	public function getURLForRoles($route = "add")
	{
		return url("/cp/board/{$this->board_uri}/roles/{$route}");
	}
	
	public function getURLForStaff($route = "add")
	{
		return url("/cp/board/{$this->board_uri}/staff/{$route}");
	}
	
	public function hasStylesheet()
	{
		return $this->getConfig('boardCustomCSS', "") != "";
	}
	
	public function getStylesheet()
	{
		return Cache::remember("board.{$this->board_uri}.stylesheet", 60, function()
		{
			$style = $this->getConfig('boardCustomCSS', "");
			
			if ($style == "" && !$this->isWorksafe())
			{
				$style = file_get_contents(public_path() . "/css/skins/yotsuba.css");
			}
			
			return $style;
		});
	}
	
	public function getThreadByBoardId($board_id)
	{
		$rememberTags    = ["board.{$this->board_uri}", "threads"];
		$rememberTimer   = 30;
		$rememberKey     = "board.{$this->board_uri}.thread.{$board_id}";
		$rememberClosure = function() use ($board_id) {
			$thread = $this->posts()
				->where('board_id', $board_id)
				->withEverythingAndReplies()
				->orderBy('bumped_last', 'desc')
				->get()
				->first();
			
			if ($thread instanceof Post)
			{
				$thread->setRelation('board', $this);
				
				foreach ($thread->replies as $reply)
				{
					$reply->setRelation('board', $this);
				}
			}
			
			return $thread;
		};
		
		switch (env('CACHE_DRIVER'))
		{
			case "file" :
			case "database" :
				$thread = Cache::remember($rememberKey, $rememberTimer, $rememberClosure);
				break;
			
			default :
				$thread = Cache::tags($rememberTags)->remember($rememberKey, $rememberTimer, $rememberClosure);
				break;
		}
		
		return $thread;
	}
	
	public function getThreads()
	{
		return $this->threads()
			->with('attachments')
			->get();
	}
	
	public function getThreadsForIndex($page = 0)
	{
		$postsPerPage = $this->getConfig('postsPerPage', 10);
		
		$rememberTags    = ["board.{$this->board_uri}.pages"];
		$rememberTimer   = 30;
		$rememberKey     = "board.{$this->board_uri}.page.{$page}";
		$rememberClosure = function() use ($page, $postsPerPage) {
			$threads = $this->threads()
				->op()
				->withEverything()
				->with(['replies' => function($query) {
					$query->forIndex();
				}])
				->orderBy('stickied', 'desc')
				->orderBy('bumped_last', 'desc')
				->skip($postsPerPage * ( $page - 1 ))
				->take($postsPerPage)
				->get();
			
			// The way that replies are fetched forIndex pulls them in reverse order.
			// Fix that.
			foreach ($threads as $thread)
			{
				$thread->setRelation('board', $this);
				$replyTake = $thread->stickied_at ? 1 : 5;
				
				$thread->body_parsed = $thread->getBodyFormatted();
				$thread->replies     = $thread->replies
					->reverse()
					->splice(-$replyTake, $replyTake);
				
				foreach($thread->replies as $reply)
				{
					$reply->setRelation('board', $this);
				}
			}
			
			return $threads;
		};
		
		switch (env('CACHE_DRIVER'))
		{
			case "file" :
			case "database" :
				$threads = Cache::remember($rememberKey, $rememberTimer, $rememberClosure);
				break;
			
			default :
				$threads = Cache::tags($rememberTags)->remember($rememberKey, $rememberTimer, $rememberClosure);
				break;
		}
		
		return $threads;
	}
	
	public function getThreadsForCatalog($page = 0)
	{
		$postsPerPage    = 100;
		
		$rememberTags    = ["board.{$this->board_uri}.pages"];
		$rememberTimer   = 30;
		$rememberKey     = "board.{$this->board_uri}.catalog";
		$rememberClosure = function() use ($page, $postsPerPage) {
			$threads = $this->threads()
				->op()
				->andAttachments()
				->andCapcode()
				->orderBy('stickied', 'desc')
				->orderBy('reply_last', 'desc')
				->skip($postsPerPage * ( $page - 1 ))
				->take($postsPerPage)
				->get();
			
			// Limit the number of attachments to one.
			foreach ($threads as $thread)
			{
				//$thread->body_parsed = $thread->getBodyFormatted();
				$thread->setRelation('board', $this);
				$thread->attachments = $thread->attachments->splice(0, 1);
			}
			
			return $threads;
		};
		
		switch (env('CACHE_DRIVER'))
		{
			case "file" :
			case "database" :
				$threads = Cache::remember($rememberKey, $rememberTimer, $rememberClosure);
				break;
			
			default :
				$threads = Cache::tags($rememberTags)->remember($rememberKey, $rememberTimer, $rememberClosure);
				break;
		}
		
		return $threads;
	}
	
	public function isWorksafe()
	{
		return !!$this->is_worksafe;
	}
	
	public function setEmailAttribute($value)
	{
		$this->attributes['email'] = empty($value) ? NULL : $value;
	}
	
	public function setOwner(User $user)
	{
		$user->forgetPermissions();
		
		$role = Role::getOwnerRoleForBoard($this);
		
		return UserRole::create([
			'user_id' => $user->user_id,
			'role_id' => $role->role_id,
		]);
	}
	
	
	public function scopeAndCreator($query)
	{
		return $query
			->join('users as creator', function($join)
			{
				$join->on('creator.user_id', '=', 'boards.created_by');
			})
			->addSelect(
				'boards.*',
				'creator.username as created_by_username'
			);
	}
	
	public function scopeAndOperator($query)
	{
		return $query
			->join('users as operator', function($join)
			{
				$join->on('operator.user_id', '=', 'boards.operated_by');
			})
			->addSelect(
				'boards.*',
				'operator.username as operated_by_username'
			);
	}
	
	public function scopeAndStaff($query)
	{
		return $query->with('staffAssignments.user');
	}
	
	public function scopeAndStaffAssignments($query)
	{
		return $query->with('staffAssignments');
	}
	
	public function scopeWhereIndexed($query)
	{
		return $query->where('is_indexed', true);
	}
	
	public function scopeWhereLastPost($query, $hours = 48)
	{
		return $query->where('last_post_at', '>=', $this->freshTimestamp()->subHours($hours));
	}
	
	public function scopeWhereNSFW($query)
	{
		return $query->where('is_worksafe', false);
	}
	
	public function scopeWhereOverboard($query)
	{
		return $query->where('is_overboard', true);
	}
	
	public function scopeWherePublic($query)
	{
		return $query;
	}
	
	public function scopeWhereSFW($query)
	{
		return $query->where('is_worksafe', true);
	}
}
