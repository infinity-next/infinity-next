<?php namespace App;

use App\Permission;
use App\Contracts\PermissionUser;

use Illuminate\Database\Eloquent\Model;

use Event;
use App\Events\RoleWasDeleted;

class Role extends Model {
	
	/**
	 * These constants represent the hard ID top-level system roles.
	 */
	const ID_ANONYMOUS     = 1;
	const ID_ADMIN         = 2;
	const ID_MODERATOR     = 3;
	const ID_OWNER         = 4;
	const ID_JANITOR       = 5;
	const ID_UNACCOUNTABLE = 6;
	const ID_REGISTERED    = 7;
	const ID_ABSOLUTE      = 8;
	
	/**
	 * These constants represent the weights of hard ID top-level system roles.
	 */
	const WEIGHT_ANONYMOUS     = 0;
	const WEIGHT_ADMIN         = 100;
	const WEIGHT_MODERATOR     = 80;
	const WEIGHT_OWNER         = 60;
	const WEIGHT_JANITOR       = 40;
	const WEIGHT_UNACCOUNTABLE = 20;
	const WEIGHT_REGISTERED    = 30;
	const WEIGHT_ABSOLUTE      = 1000;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'roles';
	
	/**
	 * The table's primary key.
	 *
	 * @var string
	 */
	protected $primaryKey = 'role_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		// These three together must be unique. (role,board_uri,caste)
		'role',       // The group name. Very loosely ties masks together.
		'board_uri',  // The board URI. Can be NULL to affect all boards.
		'caste',      // An internal name to separate roles into smaller groups.
		
		'name',       // Internal nickname. Passes through translator, so language tokens work.
		'capcode',    // Same as above, but can be null. If null, it provides no capcode when posting.
		
		'inherit_id', // PK for another Role that this directly inherits permissions from.
		'system',     // Boolean. If TRUE, it indicates the mask is a very important system role that should not be deleted.
		'weight',     // Determines the order of permissions when compiled into a mask.
	];
	
	/**
	 * Indicates their is no autoupdated timetsamps.
	 *
	 * @var boolean
	 */
	public $timestamps = false;
	
	
	public function board()
	{
		return $this->belongsTo('\App\Board', 'board_id');
	}
	
	public function inherits()
	{
		return $this->hasOne('\App\Role', 'role_id', 'inherit_id');
	}
	
	public function inheritors()
	{
		return $this->hasMany('\App\Role', 'inherit_id', 'role_id');
	}
	
	public function permissions()
	{
		return $this->belongsToMany("\App\Permission", 'role_permissions', 'role_id', 'permission_id')->withPivot('value');
	}
	
	public function users()
	{
		return $this->belongsToMany('\App\User', 'user_roles', 'role_id', 'user_id');
	}
	
	/**
	 * Ties database triggers to the model.
	 *
	 * @return void
	 */
	public static function boot()
	{
		parent::boot();
		
		// Setup event bindings...
		
		// Fire event on role being deleted.
		static::deleting(function(Role $role) {
			// Fetch our users before we detatch things.
			$users = $role->users();
			
			$role->permissions()->detach();
			$role->users()->detach();
			
			Event::fire(new RoleWasDeleted($role, $users));
			
			return true;
		});
		
	}
	
	/**
	 * Determines if this user can edit this role's permissions.
	 *
	 * @param  \App\Contracts\PermissionUser  $user
	 * @return boolean
	 */
	public function canSetPermissions(PermissionUser $user)
	{
		if (!is_null($this->board_uri))
		{
			return $user->canEditConfig($this->board_uri);
		}
		else
		{
			return $user->canAdminConfig();
		}
	}
	
	/**
	 * Returns a human-readable capcode string.
	 *
	 * @return string
	 */
	public function getCapcodeName()
	{
		if ($this->capcode)
		{
			return trans_choice((string) $this->capcode, 0);
		}
		
		return false;
	}
	/**
	 * Returns a human-readable name for this role.
	 *
	 * @return string 
	 */
	public function getDisplayName()
	{
		return trans_choice($this->name, is_null($this->board_uri) ? 0 : 1, [
			'role'      => $this->role,
			'board_uri' => $this->board_uri,
			'caste'     => $this->caste,
		]);
	}
	
	/**
	 * Returns a human-readable name for this role's group.
	 *
	 * @return string
	 */
	public function getDisplayNameForGroup()
	{
		return trans_choice("user.role.{$this->role}", is_null($this->board_uri) ? 0 : 1, [
			'role'      => $this->role,
			'board_uri' => $this->board_uri,
			'caste'     => $this->caste,
		]);
	}
	
	/**
	 * Returns a human-readable name for this role.
	 *
	 * @return string 
	 */
	public function getDisplayWeight()
	{
		return "{$this->weight} kg";
	}
	
	/**
	 * Returns owner role (found or created) for a specific board.
	 *
	 * @param  \App\Board  $board
	 * @return \App\Role
	 */
	public static function getOwnerRoleForBoard(Board $board)
	{
		return static::firstOrCreate([
			'role'       => "owner",
			'board_uri'  => $board->board_uri,
			'caste'      => NULL,
			'inherit_id' => Role::ID_OWNER,
			'name'       => "user.role.owner",
			'capcode'    => "user.role.owner",
			'system'     => false,
			'weight'     => Role::WEIGHT_OWNER + 5,
		]);
	}
	
	/**
	 * Returns the individual value for a requested permission.
	 *
	 * @param  \App\Permission  $permission
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
	
	public function getURL($route = "")
	{
		return url("/cp/roles/{$this->role_id}/{$route}");
	}
	
	public function getURLForBoard($route = "")
	{
		return url("/cp/board/{$this->board_uri}/role/{$this->role_id}/{$route}");
	}
	
	/**
	 * Returns a URL for opening this role on the site level.
	 *
	 * @return string
	 */
	public function getPermissionsURL()
	{
		return $this->getURL('permissions');
	}
	
	/**
	 * Returns a URL for opening this role in the context of the board.
	 *
	 * @return string
	 */
	public function getPermissionsURLForBoard()
	{
		return $this->getURLForBoard('permissions');
	}
	
	/**
	 * Builds a single role mask for all boards, called by name.
	 *
	 * @param  array|Collection  $roleMasks
	 * @return array
	 */
	public static function getRoleMaskByName($roleMasks)
	{
		$roles = static::whereIn('role', (array) $roleMasks)
			->orWhere('role_id', static::ID_ANONYMOUS)
			->with('permissions')
			->get();
		
		return static::getRolePermissions($roles);
	}
	
	/**
	 * Builds a single role mask for all boards, called by id.
	 *
	 * @param  array|integer  $roleIDs  Role primary keys to compile together.
	 * @return array
	 */
	public static function getRoleMaskByID($roleIDs)
	{
		$roles = static::whereIn('role_id', (array) $roleIDs)
			->orWhere('role_id', static::ID_ANONYMOUS)
			->with('permissions')
			->get();
		
		return static::getRolePermissions($roles);
	}
	
	/**
	 * Narrows query to only roles which are for a board and can be manipulated by this user.
	 *
	 * @param  \App\Board  $board
	 * @param  \App\Contracts\PermissionUser $user
	 * @return Query
	 */
	public function scopeWhereBoardRole($query, Board $board, PermissionUser $user)
	{
		return $query->where('board_uri', $board->board_uri)
			->whereLighterThanUser($user, $board)
			->orderBy('weight', 'desc');
	}
	
	/**
	 * Selects top-level roles that a user can instantiate a new caste of.
	 *
	 * @param  \App\Board  $board
	 * @param  \App\Contracts\PermissionUser $user
	 * @return Query
	 */
	public function scopeWhereCanParentForBoard($query, Board $board, PermissionUser $user)
	{
			// Only select top-level roles.
		return $query->whereStatic()
			// Only select roles that are lighter than this user.
			->whereLighterThanUser($user, $board)
			// Only select roles that can put on a single board.
			->whereLocal()
			// Do not select roles which are already being inherited,
			// unless they are a Board or Global moderator role.
			->where(function($query) use ($board, $user) {
				$query->whereDoesntHave('inheritors');
				$query->orWhereIn('role_id', [
					Role::ID_MODERATOR,
					Role::ID_JANITOR,
				]);
			});
	}
	
	/**
	 * Narrows query to only localizable roles.
	 *
	 * @return Query
	 */
	public function scopeWhereLocal($query)
	{
		return $query->where('weight', '<', static::WEIGHT_MODERATOR);
	}
	
	/**
	 * Narrows query to only system admins.
	 *
	 * @return Query
	 */
	public function scopeWhereAdmin($query)
	{
		return $query->where('role', "admin");
	}
	
	/**
	 * Narrows query to only board volunteers.
	 *
	 * @return Query
	 */
	public function scopeWhereJanitor($query)
	{
		return $query->where('role', "janitor");
	}
	
	/**
	 * Narrows query to only global moderators.
	 *
	 * @return Query
	 */
	public function scopeWhereModerator($query)
	{
		return $query->where('role', "moderator");
	}
	
	/**
	 * Narrows query to all staff roles.
	 *
	 * @return Query
	 */
	public function scopeWhereStaff($query)
	{
		return $query->whereIn('role', [
			"admin",
			"moderator",
			"janitor",
		]);
	}
	
	/**
	 * Narrows query to only roles which can be manipulated by this user.
	 *
	 * @param  \App\Contracts\PermissionUser $user
	 * @param  \App\Board  $board
	 * @return Query
	 */
	public function scopeWhereLighterThanUser($query, PermissionUser $user, Board $board = null)
	{
		return $query->where(function($query) use ($user, $board) {
				$weight = -1;
				
				if ($user->canEditConfig(null))
				{
					$weight = Role::WEIGHT_ADMIN;
				}
				else if (!is_null($board) && $user->canEditConfig($board))
				{
					$weight = Role::WEIGHT_OWNER;
				}
				
				$query->where('weight', '<', $weight);
			});
	}
	
	/**
	 * Narrows query to only roles which are top-level.
	 *
	 * @return Query
	 */
	public function scopeWhereStatic($query)
	{
		return $query->where('system', true);
	}
}
