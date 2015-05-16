<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'roles';
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */
	protected $primaryKey = 'role_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['role', 'board_uri', 'caste', 'inherits', 'name', 'capcode', 'system'];
	
	public $timestamps = false;
	
	
	public function board()
	{
		return $this->belongsTo('\App\Board', 'board_id');
	}
	
	public function inherits()
	{
		return $this->belongsTo('\App\Role', 'inherit_id', 'role_id');
	}
	
	public function permissions()
	{
		return $this->belongsToMany("\App\Permission", 'role_permissions', 'role_id', 'permission_id')->withPivot('value');
	}
	
}
