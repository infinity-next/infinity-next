<?php namespace App;

use App\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

use DB;
use Cache;
use Collection;

class Board extends Model {
	
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
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['board_uri', 'title', 'description'];
	
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
	
	
	public function posts()
	{
		return $this->hasMany('\App\Post', 'board_uri');
	}
	
	public function logs()
	{
		return $this->hasMany('\App\Log', 'board_uri');
	}
	
	public function threads()
	{
		return $this->hasMany('\App\Post', 'board_uri');
	}
	
	public function roles()
	{
		return $this->hasMany('\App\Role', 'board_uri');
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
	
	public function canPostWithoutCaptcha($user)
	{
		return $user->canPostWithoutCaptcha($this);
	}
	
	
	public function clearCachedThreads()
	{
		switch (env('CACHE_DRIVER'))
		{
			case "file" :
				break;
			
			case "database" :
				DB::table('cache')
					->where('key', 'like', "board.{$this->board_uri}.thread.%")
					->delete();
				break;
			
			default :
				Cache::tags("board.{$this->board_uri}.threads")->flush();
				break;
		}
	}
	
	public function clearCachedPages()
	{
		Cache::forget("board.{$this->board_uri}.pages");
		
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
				->orderBy('board_uri', 'asc')
				->take(20)
				->get()
		];
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
			$threadsPerPage = (int) $this->getSetting('postsPerPage');
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
			$this->settings = [
				'attachmentsMax' => 5,
				'defaultName'    => trans('board.anonymous'),
				'postMaxLength'  => 20480,
				'postsPerPage'   => 10,
			];
		}
		
		return $this->settings;
	}
	
	public function getSetting($setting, $fallback = null)
	{
		$settings = $this->getSettings();
		
		if (isset($settings[$setting]))
		{
			return $settings[$setting];
		}
		
		return $fallback;
	}
	
	public function getThread($post)
	{
		$rememberTags    = ["board.{$this->board_uri}.threads"];
		$rememberTimer   = 30;
		$rememberKey     = "board.{$this->board_uri}.thread.{$post}";
		$rememberClosure = function() use ($post) {
			return $this->posts()
				->where('board_id', $post)
				->with('attachments', 'replies', 'replies.attachments')
				->andCapcode()
				->andBan()
				->andEditor()
				->visible()
				->orderBy('reply_last', 'desc')
				->get();
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
			return $this->threads()
				->with('attachments', 'replies', 'replies.attachments')
				->andCapcode()
				->andBan()
				->andEditor()
				->op()
				->visible()
				->orderBy('stickied', 'desc')
				->orderBy('reply_last', 'desc')
				->skip($postsPerPage * ( $page - 1 ))
				->take($postsPerPage)
				->get();
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
		
		foreach ($threads as $thread)
		{
			$thread->replies = $thread->replies->take( $thread->stickied ? -1 : -5 );
		}
		
		
		return $threads;
	}
	
	
	public function scopeAndCreator($query)
	{
		return $query
			->leftJoin('users as creator', function($join)
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
			->leftJoin('users as operator', function($join)
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
