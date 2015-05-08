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
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['created_at', 'created_by', 'operated_by'];
	
	public static function getBoardListBar()
	{
		return [ static::where('posts_total', '>', '-1')
			->orderBy('uri', 'asc')
			->take(20)
			->get()
		];
	}
	
	
	public function posts()
	{
		return $this->hasMany('\App\Post', 'uri');
	}
	
	public function threads()
	{
		return $this->hasMany('\App\Post', 'uri');
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
		return $this->threads()
			->where('reply_to', null)
			->where('deleted_at', null)
			->orderBy('reply_last', 'desc')
			->skip(10 * $page)
			->take(10)
			->get();
	}
	
	public function scopeIndexed($query)
	{
		return $query;
	}
}
