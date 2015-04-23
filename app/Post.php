<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Post extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'posts';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['uri', 'board_id', 'reply_to', 'author_ip', 'author', 'email', 'body'];
	
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['author_ip'];
	
	public function board( )
	{
		return $this->belongsTo('\App\Board', 'uri');
	}
	
	public function op( )
	{
		return $this->belongsTo('\App\Post', 'id', 'reply_to');
	}
	
	public function replies( )
	{
		return $this->hasMany('\App\Post', 'reply_to', 'id');
	}
	
	
	public function getBoard( )
	{
		return $this->board()->get();
	}
	
	public function getOp( )
	{
		return $this->op()->get();
	}
	
	public function getReplies( )
	{
		return $this->replies()->get();
	}
	
	public function getRepliesForIndex( )
	{
		return $this->replies()
				->take(-5)
				->get();
	}
}
