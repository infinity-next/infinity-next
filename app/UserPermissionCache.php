<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPermissionCache extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'user_permission_cache';
	
	/**
	 * The database primary key.
	 *
	 * @var string
	 */
	protected $primaryKey = 'permission_cache_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_id', 'cache'];
	
	public $timestamps = false;
	
	
	public function permission()
	{
		return $this->belongsTo('\App\User', 'user_id');
	}
}
