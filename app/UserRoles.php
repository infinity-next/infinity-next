<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRoles extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'user_roles';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user', 'role', 'cache'];
	
	public $timestamps = false;
	
	
	public function user()
	{
		return $this->belongsTo('\App\User', 'user', 'id');
	}
	
	public function role()
	{
		return $this->belongsTo('\App\Role', 'role', 'id');
	}
	
}
