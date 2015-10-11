<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'permissions';
	
	/**
	 * The database primary key.
	 *
	 * @var string
	 */
	protected $primaryKey = 'permission_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['permission_id', 'base_value'];
	
	public $timestamps = false;
	
	
	public function roles()
	{
		return $this->belongsToMany("\App\Role", 'role_permissions', 'permission_id', 'role_id')->withPivot('value');
	}
}
