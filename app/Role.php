<?php namespace App;

use App\Permission;
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
		return $this->hasOne('\App\Role', 'role_id', 'inherit_id');
	}
	
	public function permissions()
	{
		return $this->belongsToMany("\App\Permission", 'role_permissions', 'role_id', 'permission_id')->withPivot('value');
	}
	
	public function users()
	{
		return $this->belongsToMany('\App\User', 'user_roles', 'role_id', 'user_id');
	}
	
	
	/*
	 * Returns the individual value for a requested permission.
	 *
	 * @return boolean|null
	 */
	public function getPermission(Permission $permission)
	{
		foreach ($this->permissions as $thisPermission)
		{
			if ($thisPermission->permission_id == $permission->permission_id)
			{
				return !!$thisPermission->pivot->value;
			}
		}
		
		return null;
	}
	
	
	/**
	 * Builds a single role mask for all boards, called by name.
	 *
	 * @return array
	 */
	public static function getRoleMaskByName($roleMasks)
	{
		$roles = static::whereIn('role', (array) $roleMasks)
			->orWhere('role_id', static::$ROLE_ANONYMOUS)
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
			->orWhere('role_id', static::$ROLE_ANONYMOUS)
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
			$inherited = [];
			
			if (is_numeric($role->inherit_id))
			{
				$inherited = static::getRolePermissions([$role->inherits]);
				
				foreach ($inherited as $board_uri => $inherited_permissions)
				{
					if ($board_uri == $role->board_uri || $board_uri == "")
					{
						foreach ($inherited_permissions as $permission_id => $value)
						{
							$permission = &$permissions[$role->board_uri][$permission_id];
							
							if (!isset($permission) || $permission !== 0)
							{
								$permission = (int) $value;
							}
						}
					}
				}
			}
			
			if (!isset($permissions[$role->board_uri]))
			{
				$permissions[$role->board_uri] = [];
			}
			
			foreach ($role->permissions as $permission)
			{
				$value = null;
				
				if (isset($inherited[null][$permission->permission_id]))
				{
					$value = (int) $inherited[null][$permission->permission_id];
				}
				
				if ($value !== 0)
				{
					if (isset($inherited[$role->board_uri][$permission->permission_id]))
					{
						$value = (int) $inherited[$role->board_uri][$permission->permission_id];
					}
					
					if ($value !== 0)
					{
						$value = !!$permission->pivot->value;
					}
				}
				
				if ($value)
				{
					$permissions[$role->board_uri][$permission->permission_id] = true;
				}
			}
		}
		
		return $permissions;
	}
	
}
