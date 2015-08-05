<?php namespace App;

use App\Role;
use App\User;
use App\UserRole;
use App\Services\ContentFormatter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

use DB;
use Cache;
use Collection;

use Event;
use App\Events\BoardWasCreated;

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
	 * Denotes our primary key is not an AA.
	 *
	 * @var string
	 */
	public $incrementing = false;
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['board_uri', 'title', 'description', 'created_by', 'operated_by', 'is_overboard', 'is_worksafe', 'is_indexed'];
	
	/**
	 * Cached settings for this board.
	 *
	 * @var array
	 */
	protected $settings;
	
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['created_at', 'created_by', 'operated_by'];
	
	
	/**
	 * Ties database triggers to the model.
	 *
	 * @return void
	 */
	public static function boot()
	{
		parent::boot();
		
		// Setup event bindings...
		
		// Fire events on post created.
		static::created(function(Board $board) {
			Event::fire(new BoardWasCreated($board, $board->operator));
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
	
	
	public function canAttach($user)
	{
		return $user->canAttach($this);
	}
	
	public function canBan($user)
	{
		return $user->canBan($this);
	}
	
	public function canDelete($user)
	{
		return $user->canDeleteLocally($this);
	}
	
	public function canEditConfig($user)
	{
		return $user->canEditConfig($this);
	}
	
	public function canPostWithoutCaptcha($user)
	{
		return $user->canPostWithoutCaptcha($this);
	}
	
	public function canPostInLockedThreads($user)
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
			$visibleThreads = $this->threads()->op()->visible()->count();
			$threadsPerPage = (int) $this->getSetting('postsPerPage', 10);
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
	
	public function getSettings()
	{
		if (!isset($this->settings))
		{
			$this->settings = $this->settings()->get();
		}
		
		return $this->settings;
	}
	
	public function getSetting($option_name, $fallback = null)
	{
		$settings = $this->getSettings();
		
		foreach ($settings as $setting)
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
		
		return $fallback;
	}
	
	public function getSidebarContent()
	{
		$ContentFormatter = new ContentFormatter();
		
		return $ContentFormatter->formatSidebar($this->getSetting('boardSidebarText'));
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
	
	public function hasStylesheet()
	{
		return $this->getSetting('boardCustomCSS') != "";
	}
	
	public function getStylesheet()
	{
		return Cache::remember("board.{$this->board_uri}.stylesheet", 60, function()
		{
			$style = $this->getSetting('boardCustomCSS');
			
			if ($style == "" && !$this->isWorksafe())
			{
				$style = file_get_contents(public_path() . "/css/skins/yotsuba.css");
			}
			
			return $style;
		});
	}
	
	public function getThread($post)
	{
		$rememberTags    = ["board.{$this->board_uri}", "threads"];
		$rememberTimer   = 30;
		$rememberKey     = "board.{$this->board_uri}.thread.{$post}";
		$rememberClosure = function() use ($post) {
			$replies = $this->posts()
				->where('board_id', $post)
				->withEverythingAndReplies()
				->orderBy('bumped_last', 'desc')
				->get();
			
			foreach ($replies as $reply)
			{
				$reply->body_parsed = $reply->getBodyFormatted();
			}
			
			return $replies;
		};
		
		switch (env('CACHE_DRIVER'))
		{
			case "file" :
			case "database" :
				$thread = Cache::remember($rememberKey, $rememberTimer, $rememberClosure);
				break;
			
			default :
				$thread = Cache::remember($rememberKey, $rememberTimer, $rememberClosure);
				break;
		}
		
		return $thread->first();
	}
	
	public function getThreads()
	{
		return $this->threads()
			->with('attachments')
			->get();
	}
	
	public function getThreadsForIndex($page = 0)
	{
		$postsPerPage = $this->getSetting('postsPerPage', 10);
		
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
				$replyTake = $thread->stickied_at ? 1 : 5;
				
				$thread->body_parsed = $thread->getBodyFormatted();
				$thread->replies     = $thread->replies
					->reverse()
					->splice(-$replyTake, $replyTake);
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
				->visible()
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
				$thread->body_parsed = $thread->getBodyFormatted();
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
		
		$role = Role::firstOrCreate([
			'role'       => "owner",
			'board_uri'  => $this->board_uri,
			'caste'      => NULL,
			'inherit_id' => Role::$ROLE_OWNER,
			'name'       => "Board Owner",
			'capcode'    => "Board Owner",
			'system'     => false,
		]);
		
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
	
	public function scopeIndexed($query)
	{
		return $query;
	}
}
