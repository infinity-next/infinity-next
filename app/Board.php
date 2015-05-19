<?php namespace App;

use App\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
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
	
	public function getPageCount()
	{
		$visibleThreads = $this->threads()->op()->visible()->count();
		$threadsPerPage = (int) $this->getSetting('postsPerPage');
		$pageCount      = ceil( $visibleThreads / $threadsPerPage );
		
		return $pageCount > 0 ? $pageCount : 1;
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
				'defaultName'   => trans('board.anonymous'),
				'postMaxLength' => 20480,
				'postsPerPage'  => 10,
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
		return $this->posts()
			->where('board_id', $post)
			->with('attachments', 'replies', 'replies.attachments', 'capcode', 'replies.capcode')
			->andBan()
			->andEditor()
			->visible()
			->orderBy('reply_last', 'desc')
			->get()
			->first();
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
		
		$threads = $this->threads()
			->with('attachments', 'replies', 'replies.attachments', 'capcode', 'replies.capcode')
			->andBan()
			->andEditor()
			->op()
			->visible()
			->orderBy('stickied', 'desc')
			->orderBy('reply_last', 'desc')
			->skip($postsPerPage * ( $page - 1 ))
			->take($postsPerPage)
			->get();
		
		foreach ($threads as $thread)
		{
			$thread->replies = $thread->replies->take( $thread->stickied ? -1 : -5 );
		}
		
		return $threads;
	}
	
	public function scopeIndexed($query)
	{
		return $query;
	}
}
