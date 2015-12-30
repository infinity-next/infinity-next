<?php namespace App;

use App\Contracts\PermissionUser;
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
	
	/**
	 * Returns board uris with this permission.
	 *
	 * @param  \App\Contracts\PermissionUser|null  $user  User roles must belong to. Defaults to null.
	 * @param  bool  $anonymous  Determines if we should allow generic, unassigned roles. Defaults true.
	 * @return Collection  of \App\Board->board_uri strings
	 */
	public function getBoardsWithPermissions(PermissionUser $user = null, $anonymous = true)
	{
		$userRoles = UserRole::where(function($query) use ($user, $anonymous) {
				if ($anonymous)
				{
					$query->whereNull('user_id');
				}
				
				if ($user instanceof PermissionUser && !$user->isAnonymous())
				{
					$query->orWhere('user_id', $user->user_id);
				}
				else if (!$anonymous)
				{
					$query->where(\DB::raw('0'), '1');
				}
			})
			->get()
			->pluck('role_id');
		
		if (!$userRoles)
		{
			return collect();
		}
		
		$validRoles = RolePermission::whereIn('role_id', $userRoles)
			->where('permission_id', $this->permission_id)
			->get()
			->pluck('role_id');
		
		if (!$validRoles)
		{
			return collect();
		}
		
		return Role::whereIn('role_id', $validRoles)
			->get()
			->pluck('board_uri');
	}
	
}
