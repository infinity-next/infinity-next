<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'reports';
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */
	protected $primaryKey = 'report_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['reason', 'board_uri', 'post_id', 'ip', 'user_id', 'is_dismissed', 'is_successful'];
	
	
	public function board()
	{
		return $this->belongsTo('\App\Board', 'board_uri');
	}
	
	public function post()
	{
		return $this->belongsTo('\App\Post', 'post_id');
	}
	
	public function user()
	{
		return $this->belongsTo('\App\User', 'user_id');
	}
	
	
	public function scopeWhereOpen($query)
	{
		return $query->where(function($query) {
			$query->where('is_dismissed', false);
			$query->where('is_successful', false);
		});
	}
}