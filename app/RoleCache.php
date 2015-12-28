<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class RoleCache extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'role_cache';
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */
	protected $primaryKey = 'role_cache_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_id', 'role_id'];
	
	/**
	 * Indicates if Laravel should set created_at and updated_at timestamps.
	 *
	 * @var array
	 */
	public $timestamps = false;
	
	/**
	 * Indicates if the IDs are auto-incrementing.
	 *
	 * @var array
	 */
	public $incrementing = false;
	
	
	public function user()
	{
		return $this->belongsTo('\App\User', 'user_id');
	}
	
	public function role()
	{
		return $this->belongsTo('\App\Role', 'role_id');
	}
	
}
