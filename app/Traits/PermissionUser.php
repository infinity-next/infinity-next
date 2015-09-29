<?php namespace App\Traits;

use App\Ban;
use App\Board;
use App\Permission;
use App\Post;
use App\Role;
use App\RolePermission;
use App\Contracts\PermissionUser as PermissionUserContract;

use Illuminate\Database\Eloquent\Collection;

use Request;
use Cache;

trait PermissionUser {
	
	/**
	 * Accountability is the property which determines if a user is high-risk.
	 * These users inherit different permissions and limitations to prevent
	 * attacks that involve illegal material if set to false.
	 *
	 * @var boolean
	 */
	protected $accountable;
	
	/**
	 * The $permission array is a derived set of permissions.
	 * It is associative. Each key represents a board.
	 * NULL represents global permissions.
	 * If a value is not set, it is assumed false.
	 *
	 * @var array
	 */
	protected $permissions;
	
	
	/**
	 * Getter for the $accountable property.
	 *
	 * @return boolean
	 */
	public function isAccountable()
	{
		if (config('tor.request'))
		{
			dd("Tor.");
		}
		
		if (!is_bool($this->accountable))
		{
			$this->accountable = !config('tor.request');
		}
		
		return $this->accountable;
	}
	
	/**
	 * Getter for the $anonymous property.
	 * Distinguishes this model from an Anonymous user.
	 * Applied on the model, not the trait.
	 *
	 * @return boolean
	 */
	public function isAnonymous()
	{
		return $this->anonymous;
	}
	
	
	/**
	 * Uses flexible argument options to challenge a permission/board
	 * combination against the user's permission mask.
	 *
	 * @return boolean
	 */
	public function can($permission, $board = null)
	{
		if ($permission instanceof Permission)
		{
			$permission = $permission->permission_id;
		}
		
		if ($board instanceof Board)
		{
			$board = $board->board_uri;
		}
		else if (!is_string($board) && !is_null($board))
		{
			$board = null;
		}
		
		return $this->getPermission($permission, $board);
	}
	
	/**
	 * Accepts a permission and checks if *any* board allows it.
	 *
	 * @return boolean
	 */
	public function canAny($permission)
	{
		if ($permission instanceof Permission)
		{
			$permission = $permission->permission_id;
		}
		
		foreach ($this->getPermissions() as $board_uri => $board_permissions)
		{
			if ($this->getPermission($permission, $board_uri))
			{
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Returns a list of direct, extant Board URIs where this permission exists.
	 * The goal of this is to weed out loose permissions provided by global permissions.
	 *
	 * @return array (of board_uri)
	 */
	public function canInBoards($permission)
	{
		$boards = [];
		
		if ($permission instanceof Permission)
		{
			$permission = $permission->permission_id;
		}
		
		foreach ($this->getPermissions() as $board_uri => $board_permissions)
		{
			if (strlen($board_uri) === 0)
			{
				continue;
			}
			
			if ($this->getPermission($permission, $board_uri))
			{
				$boards[] = $board_uri;
			}
		}
		
		return $boards;
	}
	
	/**
	 * Can this user administrate ANY board?
	 *
	 * @return boolean
	 */
	public function canAdminBoards()
	{
		return $this->can('sys.boards');
	}
	
	/**
	 * Can this user clear the system cache?
	 *
	 * @return boolean
	 */
	public function canAdminCache()
	{
		return $this->can('sys.cache');
	}
	
	/**
	 * Can this user administrate the system config?
	 *
	 * @return boolean
	 */
	public function canAdminConfig()
	{
		return $this->can('sys.config');
	}
	
	/**
	 * Can this user administrate the system logs?
	 *
	 * @return boolean
	 */
	public function canAdminLogs()
	{
		return $this->can('sys.logs');
	}
	
	/**
	 * Can this user administrate groups and their permissions?
	 *
	 * @return boolean
	 */
	public function canAdminRoles()
	{
		return $this->can('sys.roles');
	}
	
	/**
	 * Can this user administrate the system config?
	 *
	 * @return boolean
	 */
	public function canAdminPayments()
	{
		return $this->can('sys.payments');
	}
	
	/**
	 * Can this user administrate the system config?
	 *
	 * @return boolean
	 */
	public function canAdminPermissions()
	{
		return $this->can('sys.permissions');
	}
	
	/**
	 * Can this user administrate the system config?
	 *
	 * @return boolean
	 */
	public function canAdminTools()
	{
		return $this->can('sys.tools');
	}
	
	/**
	 * Can this user administrate the system config?
	 *
	 * @return boolean
	 */
	public function canAdminUsers()
	{
		return $this->can('sys.users');
	}
	
	/**
	 * Can this user reply with newly uploaded attachments for this board?
	 *
	 * @return boolean
	 */
	public function canAttachNew(Board $board)
	{
		// The only thing we care about for this setting is the permission mask.
		return $this->can("board.image.upload.new", $board);
	}
	
	/**
	 * Can this user reply with previously uploaded attachments for this board?
	 *
	 * @return boolean
	 */
	public function canAttachOld(Board $board)
	{
		// The only thing we care about for this setting is the permission mask.
		return $this->can("board.image.upload.old", $board);
	}
	
	/**
	 * Can this user ban others from this board?
	 *
	 * @return boolean
	 */
	public function canBan(Board $board)
	{
		// The only thing we care about for this setting is the permission mask.
		return $this->can("board.user.ban.free", $board) || $this->can("board.user.ban.reason", $board);
	}
	
	/**
	 * Can this user ban others across the entire site?
	 *
	 * @return boolean
	 */
	public function canBanGlobally()
	{
		// The only thing we care about for this setting is the permission mask.
		return $this->can("board.user.ban.free") || $this->can("board.user.ban.reason");
	}
	
	/**
	 * Can this user bumplock threads?
	 *
	 * @return boolean
	 */
	public function canBumplock(Post $post)
	{
		// We can only ever sticky a thread, for now.
		if (is_null($post->reply_to))
		{
			return $this->can("board.post.bumplock", $post->board_uri);
		}
		
		return false;
	}
	
	/**
	 * Can this user create and assume control of a new board?
	 *
	 * @return boolean
	 */
	public function canCreateBoard()
	{
		return $this->can("board.create");
	}
	
	/**
	 * Can this user create a user?
	 *
	 * @return boolean
	 */
	public function canCreateUser()
	{
		return $this->can("site.user.create");
	}
	
	/**
	 * Can this user delete this post?
	 *
	 * @return boolean
	 */
	public function canDelete(Post $post)
	{
		// If we can delete any post for this board ...
		if ($this->can("board.post.delete.other", $post->board_uri))
		{
			// Allow post deletion.
			return true;
		}
		// If the author and our current user share an IP ...
		else if ($post->author_ip == Request::ip())
		{
			// Allow post deletion, if the masks allows it.
			return $this->can("board.post.delete.self", $post->board_uri);
		}
		
		return false;
	}
	
	/**
	 * Can this user delete on this board?
	 *
	 * @return boolean
	 */
	public function canDeleteLocally(Board $board)
	{
		return $this->can("board.post.delete.other", $board);
	}
	
	/**
	 * Can this user delete posts across the entire site?
	 *
	 * @return boolean
	 */
	public function canDeleteGlobally()
	{
		return $this->can("board.post.delete.other");
	}
	
	/**
	 * Can this user edit this post?
	 *
	 * @return boolean
	 */
	public function canEdit(Post $post)
	{
		// If we can edit any post for this board ...
		if ($this->can("board.post.edit.other", $post))
		{
			// Allow post edit.
			return true;
		}
		// If the author and our current user share an IP ...
		else if ($post->author_ip == Request::ip())
		{
			// Allow post edit, if the masks allows it.
			return $this->can("board.post.edit.self", $post->board_uri);
		}
		
		return false;
	}
	
	/**
	 * Can this user edit this board's config?
	 *
	 * @return boolean
	 */
	public function canEditConfig($board = null)
	{
		return $this->can("board.config", $board);
	}
	
	/**
	 * Can this user edit any board's config?
	 *
	 * @return boolean
	 */
	public function canEditAnyConfig()
	{
		return $this->canAny("board.config");
	}
	
	/**
	 * Can this user edit this staff member on this board?
	 *
	 * @return boolean
	 */
	public function canEditBoardStaffMember(PermissionUserContract $user, Board $board)
	{
		if ($this->canAdminConfig())
		{
			return true;
		}
		
		if ($user->user_id == $this->user_id)
		{
			return false;
		}
		
		return $this->can("board.config", $board);
	}
	
	/**
	 * Can this user edit a board's URI?s
	 *
	 * @return boolean
	 */
	public function canEditBoardUri(Board $board)
	{
		return false;
	}
	
	/**
	 * Can this user lock this thread to replies?
	 *
	 * @return boolean
	 */
	public function canLock(Post $post)
	{
		// We can only ever sticky a thread, for now.
		if (is_null($post->reply_to))
		{
			return $this->can("board.post.lock", $post);
		}
		
		return false;
	}
	
	/**
	 * Can this user post in locked threads?
	 *
	 * @return boolean
	 */
	public function canPostInLockedThreads(Board $board = null)
	{
		return $this->can('board.post.lock_bypass', $board);
	}
	
	/**
	 * Can this user post in this thread without filling out the captcha?
	 *
	 * @return boolean
	 */
	public function canPostWithoutCaptcha(Board $board = null)
	{
		return $this->can('sys.nocaptcha', $board);
	}
	
	/**
	 * Can this user post a new reply to an existing thread
	 *
	 * @return boolean
	 */
	public function canPostReply(Board $board = null)
	{
		return $this->can('board.post.create.reply', $board);
	}
	
	/**
	 * Can this user post a new thread
	 *
	 * @return boolean
	 */
	public function canPostThread(Board $board = null)
	{
		return $this->can('board.post.create.thread', $board);
	}
	
	/**
	 * Can this user report this post?
	 *
	 * @return boolean
	 */
	public function canReport(Post $post)
	{
		return $this->can('board.post.report', $post->board_uri);
	}
	
	/**
	 * Can this user report this post?
	 *
	 * @return boolean
	 */
	public function canReportGlobally(Post $post)
	{
		return $this->can('site.post.report');
	}
	
	/**
	 * Can this user view a board's reports?
	 *
	 * @return boolean
	 */
	public function canViewReports($board = null)
	{
		if ($board instanceof Board)
		{
			return $this->canAny('board.reports', $board->board_uri);
		}
		
		return $this->canAny('board.reports');
	}
	
	/**
	 * Can this user report this post?
	 *
	 * @return boolean
	 */
	public function canViewReportsGlobally()
	{
		return $this->can('site.reports');
	}
	
	/**
	 * Can this user sticky a thread?
	 *
	 * @return boolean
	 */
	public function canSticky(Post $post)
	{
		// We can only ever sticky a thread, for now.
		if (is_null($post->reply_to))
		{
			return $this->can("board.post.sticky", $post->board_uri);
		}
		
		return false;
	}
	
	/**
	 * Can this user see another's raw IP?
	 *
	 * @return boolean
	 */
	public function canViewRawIP()
	{
		return $this->can('site.user.raw_ip');
	}
	
	/**
	 * Drops the user's permission cache.
	 *
	 * @return void.
	 */
	public function forgetPermissions()
	{
		Cache::forget("user.{$this->user_id}.permissions");
	}
	
	/**
	 * Returns a complete list of roles that this user may delegate to others.
	 *
	 * @param  Board  $board
	 * @return Collection|array
	 */
	public function getAssignableRolesForBoard(Board $board)
	{
		$roles = [];
		
		if ($this->can('board.user.role', $board))
		{
			return $board->roles()
				->whereLevel(Role::ID_JANITOR)
				->get();
		}
		
		return $roles;
	}
	
	/**
	 * Returns a list of board_uris where the canEditConfig permission is given.
	 *
	 * @return Collection  of Board
	 */
	public function getBoardsWithAssetRights()
	{
		return $this->getBoardsWithConfigRights();
	}
	
	/**
	 * Returns a list of board_uris where the canEditConfig permission is given.
	 *
	 * @return Collection  of Board
	 */
	public function getBoardsWithConfigRights()
	{
		$whitelist = true;
		$boardlist = [];
		
		if ($this->canEditConfig(null))
		{
			$whitelist = false;
		}
		
		foreach ($this->getPermissions() as $board_uri => $permission)
		{
			if ($this->canEditConfig($board_uri) === $whitelist)
			{
				$boardlist[] = $board_uri;
			}
		}
		
		$boardlist = array_unique($boardlist);
		
		return Board::where(function($query) use ($whitelist, $boardlist) {
				if ($whitelist)
				{
					$query->whereIn('board_uri', $boardlist);
				}
				else
				{
					$query->whereNotIn('board_uri', $boardlist);
				}
			})
			->andCreator()
			->andOperator()
			->andStaffAssignments()
			->get();
	}
	
	/**
	 * Returns a list of board_uris where the canEditConfig permission is given.
	 *
	 * @return Collection  of Board
	 */
	public function getBoardsWithStaffRights()
	{
		return $this->getBoardsWithConfigRights();
	}
	
	/**
	 * Gets the user's roles with capcodes for this board.
	 * A capcode is a text colum associated with a role.
	 *
	 * @param  \App\Board  $board
	 * @return array|Collection
	 */
	public function getCapcodes(Board $board)
	{
		if (!$this->isAnonymous())
		{
			// Only return roles 
			return $this->roles->filter(function($role) use ($board) {
				
				if (!$role->capcode)
				{
					return false;
				}
				
				if (is_null($role->board_uri) || $role->board_uri == $board->board_uri)
				{
					return true;
				}
			});
		}
		
		return [];
	}
	
	/**
	 * Returns the name of the user that should be displayed in public.
	 *
	 * @return string
	 */
	public function getDisplayName()
	{
		return $this->isAnonymous() ? trans('board.anonymous') : $this->username;
	}
	
	/**
	 * Determine the user's permission for a specific item.
	 *
	 * @param  string  $permission  The permission ID we are checking.
	 * @param  string|null  $board_uri  The board URI we're checking against. NULL means global only.
	 * @return boolean
	 */
	protected function getPermission($permission, $board_uri = null)
	{
		$permissions = $this->getPermissions();
		
		// Check for a localized permisison.
		if (isset($permissions[$board_uri][$permission]))
		{
			return $permissions[$board_uri][$permission];
		}
		// Check for a global permission.
		else if (isset($permissions[null][$permission]))
		{
			return $permissions[null][$permission];
		}
		
		// Assume false if not explicitly set.
		return false;
	}
	
	/**
	 * Returns permissions for all boards belonging to our current route.
	 *
	 * @return array
	 */
	protected function getPermissions()
	{
		// Default permission mask is normal.
		$mask = "normal";
		
		// If the user is from Tor, they are instead unaccountable.
		if (!$this->isAccountable())
		{
			$mask = "unaccountable";
		}
		
		return $this->getPermissionsWithRoutes($mask);
	}
	
	/**
	 * Returns permission masks for each route.
	 * This is where permissions are interpreted.
	 *
	 * @return array
	 */
	protected function getPermissionMasks()
	{
		$permissions = [];
		$routes      = $this->getPermissionRoutes();
		
		// There are two kinds of permission assignments.
		// 1. Permissions that belong to the route.
		// 2. Permissions directly assigned to the user.
		// 
		// When a permission is a part of a major mask branch (identified in getPermissionRoutes),
		// then any role with that role name becomes a part of the mask.
		// 
		// When a permission is directly assigned to the user, then only that mask and its
		// inherited mask are incorporated. Inheritance only goes up one step for right now.
		
		$allGroups = [];
		
		// Pull each route and add its groups to the master collection.
		foreach ($routes as $branch => $roleGroups)
		{
			$allGroups = array_merge($allGroups, $roleGroups);
		}
		
		// We only want uniques.
		$allGroups = array_unique($allGroups);
		
		
		// Write out a monster query to pull precisely what we need to build our permission masks.
		$allRoles = Role::where(function($query) use ($allGroups)
			{
				// Pull any role that belongs to our masks's route.
				$query->whereIn('role', $allGroups);
				
				// If we're not anonymous, we also need directly assigned roles.
				if (!$this->isAnonymous())
				{
					$query->orWhereHas('users', function($query) {
						$query->where( \DB::raw("`user_roles`.`user_id`"), $this->user_id);
					});
				}
			})
			// Gather our inherited roles, their permissions, and our permissions.
			->with('inherits')
			->with('permissions')
			->with('inherits.permissions')
			// Execute query
			->get()
			// Remove redundant roles, in case they exist.
			->unique(function($role) {
				return $role->{$role->getKeyName()};
			})
			// Finally, sort by weight, ascending.
			->sortBy(function($role) {
				return $role->weight;
			});
		
		// In order to determine if we want to include a role in a specific mask,
		// we must also pull a user's roles to see what is directly applied to them.
		$userRoles = $this->getRoles()->modelKeys();
		
		// Okay!
		// With our roles fresh off out the db, we can now begin to assemble the masks.
		// Loop through each route again.
		foreach ($routes as $branch => $roleGroups)
		{
			$permissions[$branch] = [];
			
			// Loop through each role.
			foreach ($allRoles as $role)
			{
				// Check to see if it's either directly assigned to us or in the mask's route.
				if (in_array($role->role, $roleGroups) || in_array($role->role_id, $userRoles))
				{
					// This role IS applicable to this branch.
					
					// Create a new array for this board if required.
					if (!isset($permissions[$branch][$role->board_uri]))
					{
						$permissions[$branch][$role->board_uri] = [];
					}
					
					// Loop through each role's permission and set them on the respective jurisdiction.
					foreach ($role->permissions as $permission)
					{
						$permissions[$branch][$role->board_uri][$permission->permission_id] = $permission->pivot->value == 1;
					}
					
					// Loop through each inherited permission as well.
					if ($role->inherit_id)
					{
						foreach ($role->inherits->permissions as $permission)
						{
							$permissions[$branch][$role->board_uri][$permission->permission_id] = $permission->pivot->value == 1;
						}
					}
					
					// Additionally, if our permission is set on the global level, we must also go into each
					// lesser jurisdiction and unset their rule because it no longer matters.
					if (is_null($role->board_uri))
					{
						foreach ($permissions[$branch] as $board_uri => $boardPermissions)
						{
							if ((string) $board_uri != "")
							{
								unset($boardPermissions[$permission->permission_id]);
							}
						}
					}
				}
			}
			
			// Clean up the permission mask and remove empty rulesets.
			foreach ($permissions[$branch] as $board_uri => $boardPermissions)
			{
				if (!count($boardPermissions))
				{
					unset($permissions[$branch][$board_uri]);
				}
			}
		}
		
		return $permissions;
	}
	
	/**
	 * Returns a complete array of all possible routes and what roles belong to them.
	 *
	 * @return array
	 */
	protected function getPermissionRoutes()
	{
		// When building a permission mask, there are two main branches we can take.
		// "Normal", and "Unaccountable".
		// 
		// When the permission mask is finalized, it will still have these two branches.
		// But, depending on the user's conditions, it may have alternate routes within.
		// 
		// This is set up with the hope that we will be easily able to change how permission
		// masks are build in the future. Keep in mind that the masks's individual weights
		// still matter when determining what the user can actually do.
		
		$routes = [
			'normal'        => [],
			'unaccountable' => [],
		];
		
		// Both branches base off anonymous.
		$routes['normal'][]        = "anonymous";
		$routes['unaccountable'][] = "anonymous";
		
		// The unaccountable branch uses a special mask.
		// This would generally be for Tor users.
		$routes['unaccountable'][] = "unaccountable";
		
		// Finally, if the user is registered, we add another mask.
		// This is a bit of a placeholder. There are no permissions
		// by default that only affect registered users.
		if (!$this->isAnonymous())
		{
			$routes['normal'][]        = "registered";
			$routes['unaccountable'][] = "registered";
		}
		
		return $routes;
	}
	
	/**
	 * Return the user's entire permission object,
	 * build it if nessecary.
	 *
	 * @param  string  $route
	 * @return array
	 */
	protected function getPermissionsWithRoutes($route = null)
	{
		if (!isset($this->permissions))
		{
			$rememberTags    = ["user.{$this->user_id}", "permissions"];
			$rememberTimer   = 3600;
			$rememberKey     = "user.{$this->user_id}.permissions";
			$rememberClosure = function()
			{
				return $this->getPermissionMasks();
			};
			
			// return $rememberClosure();
			
			switch (env('CACHE_DRIVER'))
			{
				case "file" :
				case "database" :
					$this->permissions = Cache::remember($rememberKey, $rememberTimer, $rememberClosure);
					break;
				
				default :
					$this->permissions = Cache::tags($rememberTags)->remember($rememberKey, $rememberTimer, $rememberClosure);
					break;
			}
		}
		
		if (!is_null($route))
		{
			return $this->permissions[$route];
		}
		
		return $this->permissions;
	}
	
	/**
	 * Returns a collection of roles directly assigned to this user.
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function getRoles()
	{
		if ($this->isAnonymous())
		{
			return new Collection();
		}
		
		return $this->roles()->get();
	}
	
	/**
	 * Returns a human-readable username HTML string with a profile link.
	 *
	 * @return string  HTML
	 */
	public function getUsernameHTML()
	{
		if ($this->isAnonymous())
		{
			return "<span class=\"username\">Anonymous</span>";
		}
		
		return "<a href=\"{$this->getUserURL()}\" class=\"username\">{$this->username}</a>";
	}
	
	/**
	 * Returns a link to the user's public profile page.
	 *
	 * @return string  URL
	 */
	public function getUserURL()
	{
		return "/cp/user/{$this->username}.{$this->user_id}/";
	}
	
	/**
	 * Returns a human-readable IP address based on user permissions.
	 * This will obfuscate it if we do not have permission to view raw IPs.
	 *
	 * @param  string  $ip  Normal IP string.
	 * @return string  Either $ip or an ip_less version.
	 */
	public function getTextForIP($ip)
	{
		if ($this->canViewRawIP())
		{
			return $ip;
		}
		
		return ip_less($ip);
	}
}
