<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model {
	
	/**
	 * These static variables represent the hard ID top-level roles.
	 */
	public static $ROLE_ANONYMOUS     = 1;
	public static $ROLE_ADMIN         = 2;
	public static $ROLE_MODERATOR     = 3;
	public static $ROLE_OWNER         = 4;
	public static $ROLE_VOLUTNEER     = 5;
	public static $ROLE_UNACCOUNTABLE = 6;
	
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
	protected $fillable = ['role', 'board_uri', 'caste', 'inherit_id', 'name', 'capcode', 'system'];
	
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
	
	
	/**
	 * Builds a single role mask for all boards, called by name.
	 *
	 * @return array
	 */
	public static function getRoleMaskByName($roleMasks)
	{
		$roles = static::whereIn('role', (array) $roleMasks)
			->with('permissions')
			->get();
		
		return static::getRolePermissions($roles);
	}
	
	/**
	 * Builds a single role mask for all boards, called by id.
	 *
	 * @return array
	 */
	public static function getRoleMaskByID($roleIDs)
	{
		$roles = static::whereIn('role_id', (array) $roleIDs)
			->with('permissions')
			->get();
		
		return static::getRolePermissions($roles);
	}
	
	/**
	 * Compiles a set of roles into a permission mask.
	 *
	 * @return array
	 */
	protected static function getRolePermissions($roles)
	{
		$permissions = [];
		
		foreach ($roles as $role)
		{
			if (!isset($permissions[$role->board_uri]))
			{
				$permissions[$role->board_uri] = [];
			}
			
			foreach ($role->permissions as $permission)
			{
				$permissions[$permission->board_uri][$permission->permission_id] = !!$permission->pivot->value;
			}
		}
		
		return $permissions;
	}
}
