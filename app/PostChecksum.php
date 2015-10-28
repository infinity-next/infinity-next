<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class PostChecksum extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'post_checksums';
	
	/**
	 * The database primary key.
	 *
	 * @var string
	 */
	protected $primaryKey = 'post_checksum_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['board_uri', 'checksum'];
	
	/**
	 * Indicates their is no autoupdated timetsamps.
	 *
	 * @var boolean
	 */
	public $timestamps = false;
	
	
	public function post()
	{
		return $this->belongsTo('\App\Board', 'board_uri');
	}
}
