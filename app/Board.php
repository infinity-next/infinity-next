<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

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
	protected $primaryKey = 'uri';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['uri', 'title', 'description'];
	
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
		return $this->hasMany('\App\Post', 'uri');
	}
	
	public function threads()
	{
		return $this->hasMany('\App\Post', 'uri');
	}
	
	
	public function canAttach($user = null)
	{
		if ($user instanceof \App\User)
		{
			return $user->canAttach($this);
		}
		
		return false;
	}
	
	
	public static function getBoardListBar()
	{
		return [
			static::where('posts_total', '>', '-1')
				->orderBy('uri', 'asc')
				->take(20)
				->get()
		];
	}
	
	public function getPageCount()
	{
		return ceil( $this->threads()->op()->visible()->count() / (int) $this->getSetting('postsPerPage') );
	}
	
	public function getSettings()
	{
		if (!isset($this->settings))
		{
			$this->settings = [
				'defaultName'   => trans('board.anonymous'),
				'postMaxLength' => 2048,
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
	
	public function getLocalThread($post)
	{
		return $this->threads()
			->where('board_id', $post)
			->first();
	}
	
	public function getThreads()
	{
		return $this->threads()->get();
	}
	
	public function getThreadsForIndex($page = 0)
	{
		$postsPerPage = $this->getSetting('postsPerPage', 10);
		
		return $this->threads()
			->op()
			->visible()
			->orderBy('reply_last', 'desc')
			->skip($postsPerPage * ( $page - 1 ))
			->take($postsPerPage)
			->get();
	}
	
	public function scopeIndexed($query)
	{
		return $query;
	}
}
