<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class RolePermissions extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'role_permissions';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['role', 'permission', 'value'];
	
	public $timestamps = false;
	
	
	public function permission()
	{
		return $this->belongsTo('\App\Permission', 'permission');
	}
	
	public function role()
	{
		return $this->belongsTo('\App\Role', 'role', 'id');
	}
	
}
