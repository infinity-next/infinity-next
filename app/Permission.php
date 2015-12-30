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
		// Identify roles which affect this user.
		// Sometimes we will only want direct assignments.
		// This includes null user_id assignments for anonymouse users.
		$userRoles = UserRole::select('role_id')
			->where(function($query) use ($user, $anonymous) {
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
		
		$inheritRoles = Role::select('role_id', 'inherit_id')
			->whereIn('role_id', $userRoles)
			->get()
			->pluck('inherit_id')
			->filter(function($item) {
				return !is_null($item);
			});
		
		// Identify roles which use this permission,
		// or which borrow inherited roles.
		$validRoles = RolePermission::select('role_id', 'permission_id')
			->where(function($query) use ($userRoles, $inheritRoles) {
				$query->orWhereIn('role_id', $userRoles);
				
				if ($inheritRoles)
				{
					$query->orWhereIn('role_id', $inheritRoles);
				}
			})
			->where('permission_id', $this->permission_id)
			->get()
			->pluck('role_id');
		
		if (!$validRoles)
		{
			return collect();
		}
		
		// Find the intersection of roles we have and roles we want.
		$intersectIdents = collect($userRoles)->intersect(collect($validRoles));
		$inheritIdents = collect($inheritRoles)->intersect(collect($validRoles));
		$intersectRoles = collect();
		
		if ($intersectIdents)
		{
			// These are only roles which are directly assigned to us with
			// this permission.
			$intersectRoles = collect(Role::select('role_id', 'board_uri')
				->whereIn('role_id', $intersectIdents)
				->get()
				->pluck('board_uri'));
		}
		
		if ($inheritIdents)
		{
			$intersectRoles = collect(Role::select('role_id', 'board_uri')
				->whereIn('inherit_id', $inheritIdents)
				->whereIn('role_id', $userRoles)
				->get()
				->pluck('board_uri'))
				->merge($intersectRoles);
		}
		
		return $intersectRoles;
	}
	
}
