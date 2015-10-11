<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class PostCite extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'post_cites';
	
	/**
	 * The database primary key.
	 *
	 * @var string
	 */
	protected $primaryKey = 'post_cite_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['post_id', 'post_board_uri', 'post_board_id', 'cite_id', 'cite_board_uri', 'cite_board_id'];
	
	/**
	 * Indicates their is no autoupdated timetsamps.
	 *
	 * @var boolean
	 */
	public $timestamps = false;
	
	
	public function post()
	{
		return $this->belongsTo('\App\Post', 'post_id');
	}
	
	public function postBorad()
	{
		return $this->belongsTo('\App\Board', 'board_uri', 'post_board_uri');
	}
	
	public function cite()
	{
		return $this->belongsTo('\App\Post', 'cite_id', 'post_id');
	}
	
	public function citeBoard()
	{
		return $this->belongsTo('\App\Board', 'board_uri', 'cite_board_uri');
	}
}
