<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'pages';
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */
	protected $primaryKey = 'page_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'board_uri',
		'name',
		'title',
		'body',
		'body_parsed',
		'body_parsed_at',
	];
	
	/**
	 * Attributes which are automatically sent through a Carbon instance on load.
	 *
	 * @var array
	 */
	protected $dates = [
		'created_at',
		'updated_at',
		'deleted_at',
		'body_parsed_at',
	];
	
	public function board()
	{
		return $this->belongsTo('\App\Board', 'board_uri');
	}
	
}
